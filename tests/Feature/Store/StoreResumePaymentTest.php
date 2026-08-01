<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\Player;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Tests\Feature\Store\FakeStripeGateway;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    // Stands in for the real Stripe driver, which needs an account to say anything at all.
    config(['store.gateways.stripe' => FakeStripeGateway::class]);
    FakeStripeGateway::reset();

    $this->baseCurrency();

    $this->enableStoreGateways(['manual', 'stripe']);

    $this->withCookie(StoreCartService::COOKIE, 'guest-resume-token');

    // StorePlayerResolver short-circuits on a player it already knows. Without this, every
    // checkout below reaches the live api.minecraftservices.com lookup for "Steve" — which
    // Mojang rate-limits, so the suite failed at random once it had been run enough times in a
    // row. The same reason StoreCheckoutTest seeds this player.
    Player::factory()->create(['username' => 'Steve']);

    $this->withoutMiddleware([
        ThrottleRequests::class,
        ThrottleRequestsWithRedis::class,
    ]);
});

/**
 * Place a real pending order through checkout, so its payment row is the one the app made.
 */
function pendingOrderPaidBy(string $gateway): StoreOrder
{
    $package = StorePackage::factory()->create(['price' => 850]);

    test()->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    test()->post(route('store.checkout.store'), [
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => $gateway,
        'accept_terms' => true,
    ]);

    return StoreOrder::latest('id')->firstOrFail();
}

// -- What the pending screen offers ------------------------------------------------------------

test('a pending order is offered every gateway that can charge its currency', function () {
    $order = pendingOrderPaidBy('stripe');

    $this->get(route('store.order.result', $order->uuid))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('order.gateway', 'stripe')
            ->count('gateways', 2)
        );
});

test('a paid order is offered nothing to pay with', function () {
    $order = pendingOrderPaidBy('stripe');
    $order->update(['status' => StoreOrderStatus::PAID, 'paid_at' => now()]);

    $this->get(route('store.order.result', $order->uuid))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->count('gateways', 0));
});

test('an order past the pending window is offered nothing to pay with', function () {
    // The sweeper is about to cancel it, and a capture against a cancelled order is money that
    // markPaid() will not credit.
    config(['store.pending_order_ttl_hours' => 24]);
    $order = pendingOrderPaidBy('stripe');
    $order->update(['created_at' => now()->subHours(25)]);

    $this->get(route('store.order.result', $order->uuid))
        ->assertInertia(fn ($page) => $page->count('gateways', 0));

    $this->post(route('store.order.pay', $order->uuid))
        ->assertRedirect(route('store.order.result', $order->uuid));

    expect(FakeStripeGateway::$created)->toHaveCount(1, 'Only the original checkout, no retry.');
});

// -- Resuming ----------------------------------------------------------------------------------

test('continuing a payment returns to the very same session', function () {
    // The rule the whole feature turns on: one order, one live session. Opening a second is how a
    // buyer ends up able to pay twice for one order.
    $order = pendingOrderPaidBy('stripe');
    $sessionId = $order->payments()->first()->gateway_session_id;

    $this->post(route('store.order.pay', $order->uuid))
        ->assertRedirect('https://fake-gateway.test/resume/'.$sessionId);

    expect(FakeStripeGateway::$created)->toHaveCount(1, 'No second session was opened.');
    expect($order->payments()->count())->toBe(1);
});

test('a dead session is replaced rather than reopened', function () {
    $order = pendingOrderPaidBy('stripe');
    FakeStripeGateway::$sessionIsOpen = false;

    $response = $this->post(route('store.order.pay', $order->uuid));

    expect(FakeStripeGateway::$created)->toHaveCount(2, 'The expired session was replaced.');
    // Reused row, new session on it: the attempt is the same one, it just needed a fresh checkout.
    expect($order->payments()->count())->toBe(1);

    $payment = $order->payments()->first();
    $response->assertRedirect('https://fake-gateway.test/pay/'.$payment->uuid);
    expect($payment->gateway_session_id)->toBe('sess_'.$payment->uuid);
});

test('paying an order that is already paid just returns to the receipt', function () {
    $order = pendingOrderPaidBy('stripe');
    $order->update(['status' => StoreOrderStatus::PAID, 'paid_at' => now()]);

    $this->post(route('store.order.pay', $order->uuid))
        ->assertRedirect(route('store.order.result', $order->uuid));

    expect(FakeStripeGateway::$created)->toHaveCount(1);
});

// -- Switching gateway -------------------------------------------------------------------------

test('switching gateway closes the session left behind', function () {
    // Without this the buyer could pay the abandoned card checkout after settling up elsewhere,
    // and the gateway would capture money against an order that is already paid.
    $order = pendingOrderPaidBy('stripe');
    $original = $order->payments()->first();

    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'manual'])
        ->assertRedirect(route('store.order.result', $order->uuid));

    expect(FakeStripeGateway::$abandoned)->toBe([$original->gateway_session_id]);
    expect($original->fresh()->status)->toBe(StorePaymentStatus::FAILED);
});

test('switching gateway opens a fresh attempt on the new one', function () {
    $order = pendingOrderPaidBy('stripe');

    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'manual']);

    expect($order->payments()->count())->toBe(2);

    $latest = $order->payments()->latest('id')->first();
    expect($latest->gateway->value)->toBe('manual');
    expect($latest->status)->toBe(StorePaymentStatus::PENDING);
    expect((int) $latest->amount)->toBe((int) $order->amount_due);

    // So the pending screen names the method they are actually using now.
    expect($order->fresh()->gateway->value)->toBe('manual');
});

test('switching onto a hosted gateway sends the buyer to it', function () {
    $order = pendingOrderPaidBy('manual');

    $response = $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'stripe']);

    $payment = $order->payments()->latest('id')->first();
    $response->assertRedirect('https://fake-gateway.test/pay/'.$payment->uuid);
    expect($payment->gateway->value)->toBe('stripe');
});

test('only one pending payment survives a switch', function () {
    // Two pending rows would make "which session is live" unanswerable.
    $order = pendingOrderPaidBy('stripe');

    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'manual']);
    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'stripe']);

    expect($order->payments()->where('status', StorePaymentStatus::PENDING)->count())->toBe(1);
});

test('a gateway that is switched off is refused', function () {
    $order = pendingOrderPaidBy('stripe');

    $this->enableStoreGateways(['stripe']);

    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'manual'])
        ->assertSessionHasErrors(['gateway']);

    expect($order->payments()->count())->toBe(1);
});

test('an unknown gateway is refused', function () {
    $order = pendingOrderPaidBy('stripe');

    $this->post(route('store.order.pay', $order->uuid), ['gateway' => 'bitcoin'])
        ->assertSessionHasErrors(['gateway']);
});

test('somebody elses order cannot be paid', function () {
    // The uuid is the credential for a guest, but an order owned by an account is not open season.
    $order = pendingOrderPaidBy('stripe');
    $order->update(['user_id' => User::factory()->create()->id]);

    $this->post(route('store.order.pay', $order->uuid))->assertStatus(403);
});

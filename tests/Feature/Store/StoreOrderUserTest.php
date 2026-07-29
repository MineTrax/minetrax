<?php

use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderPaid;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Models\User;
use App\Notifications\StoreOrderPaidNotification;
use App\Notifications\StoreOrderPlacedStaffNotification;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Queue::fake([ProcessStoreOrderPurchaseJob::class]);
});

function orderWithItem(array $attributes = []): StoreOrder
{
    $order = StoreOrder::factory()->create(array_merge([
        'total' => 1500, 'amount_due' => 1500, 'currency' => 'USD',
    ], $attributes));

    $package = StorePackage::factory()->create();

    $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 2,
        'unit_price_original' => 750,
        'unit_price' => 750,
        'total' => 1500,
    ]);

    return $order->fresh('items');
}

test('a guest cannot reach the purchase history', function () {
    $this->get(route('store.my-order.index'))->assertRedirect(route('login'));
});

test('a user sees only their own orders', function () {
    $user = User::factory()->create();
    orderWithItem(['user_id' => $user->id]);
    orderWithItem(['user_id' => User::factory()->create()->id]);
    orderWithItem(['user_id' => null]);

    $this->actingAs($user)
        ->get(route('store.my-order.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Store/IndexMyStoreOrder')
            ->has('orders.data', 1)
        );
});

test('a user cannot open someone elses order', function () {
    $order = orderWithItem(['user_id' => User::factory()->create()->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('store.my-order.show', $order->uuid))
        ->assertNotFound();
});

test('a user cannot open a guest order from their history', function () {
    $order = orderWithItem(['user_id' => null]);

    $this->actingAs(User::factory()->create())
        ->get(route('store.my-order.show', $order->uuid))
        ->assertNotFound();
});

test('the order detail renders for its owner', function () {
    $user = User::factory()->create();
    $order = orderWithItem(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('store.my-order.show', $order->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Store/ShowMyStoreOrder')
            ->where('order.uuid', $order->uuid)
            ->has('order.items', 1)
            ->has('order.money.total')
        );
});

test('the history is hidden when the store is disabled', function () {
    config(['store.enabled' => false]);

    $this->actingAs(User::factory()->create())
        ->get(route('store.my-order.index'))
        ->assertForbidden();
});

test('a guest can open their own order by uuid', function () {
    // The uuid is the credential: a guest has no account to authorise against.
    $order = orderWithItem(['user_id' => null]);

    $this->get(route('store.order.result', $order->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Store/ResultStoreOrder'));
});

test('a stranger cannot open a signed in users order', function () {
    $order = orderWithItem(['user_id' => User::factory()->create()->id]);

    $this->get(route('store.order.result', $order->uuid))->assertForbidden();
});

test('the status endpoint reports both states', function () {
    $order = orderWithItem(['user_id' => null]);

    $this->getJson(route('store.order.status', $order->uuid))
        ->assertOk()
        ->assertJson(['status' => 'pending', 'delivery_status' => 'pending']);
});

// --- Notifications --------------------------------------------------------------------------
function markPaid(StoreOrder $order): void
{
    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => $order->amount_due,
        'currency' => $order->currency,
        'status' => StorePaymentStatus::PENDING,
    ]);

    app(StoreOrderService::class)->markPaid($order, $payment, (int) $order->amount_due, $order->currency);
}

test('a signed in buyer gets a receipt', function () {
    Notification::fake();

    $user = User::factory()->create();
    markPaid(orderWithItem(['user_id' => $user->id]));

    Notification::assertSentTo($user, StoreOrderPaidNotification::class);
});

test('a guest buyer gets a receipt at the address they gave', function () {
    Notification::fake();

    $order = orderWithItem(['user_id' => null, 'email' => 'buyer@example.com']);
    markPaid($order);

    Notification::assertSentTo(
        new AnonymousNotifiable,
        StoreOrderPaidNotification::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'buyer@example.com'
    );
});

test('a guest who gave no email gets nothing rather than an error', function () {
    Notification::fake();

    markPaid(orderWithItem(['user_id' => null, 'email' => null]));

    Notification::assertNothingSentTo(new AnonymousNotifiable);
});

test('staff are notified of a new order', function () {
    Notification::fake();

    $superadmin = User::whereId(1)->first();
    markPaid(orderWithItem());

    Notification::assertSentTo($superadmin, StoreOrderPlacedStaffNotification::class);
});

test('staff are not notified when the setting is off', function () {
    Notification::fake();

    $settings = app(StoreSettings::class);
    $settings->notify_staff_on_purchase = false;
    $settings->save();

    markPaid(orderWithItem());

    Notification::assertNotSentTo(User::whereId(1)->first(), StoreOrderPlacedStaffNotification::class);
});

test('a replayed payment does not send a second receipt', function () {
    Notification::fake();

    $user = User::factory()->create();
    $order = orderWithItem(['user_id' => $user->id]);

    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => $order->amount_due,
        'currency' => $order->currency,
    ]);

    $orders = app(StoreOrderService::class);
    $orders->markPaid($order, $payment, (int) $order->amount_due, $order->currency);
    $orders->markPaid($order->fresh(), $payment, (int) $order->amount_due, $order->currency);

    Notification::assertSentToTimes($user, StoreOrderPaidNotification::class, 1);
});

test('the receipt formats money in the orders own currency', function () {
    StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'symbol' => '¥', 'is_base' => false, 'rate_to_base' => 150]);

    $order = orderWithItem(['currency' => 'JPY', 'total' => 1000, 'amount_due' => 1000]);
    $mail = (new StoreOrderPaidNotification($order))->toMail(new AnonymousNotifiable);

    // A thousand yen, not ten. The receipt goes to a human who will notice.
    $this->assertStringContainsString('¥1,000', implode(' ', $mail->introLines));
});

test('the receipt links to the result page', function () {
    $order = orderWithItem();
    $mail = (new StoreOrderPaidNotification($order))->toMail(new AnonymousNotifiable);

    expect($mail->actionUrl)->toEqual(route('store.order.result', $order->uuid));
});

test('the checkout page redirects when the cart is empty', function () {
    $this->get(route('store.checkout.create'))->assertRedirect(route('store.cart.show'));
});

test('an active grant is shown on the order detail', function () {
    $user = User::factory()->create();
    $order = orderWithItem(['user_id' => $user->id]);
    $item = $order->items->first();

    $item->grant()->create([
        'store_package_id' => $item->store_package_id,
        'player_uuid' => $order->player_uuid,
        'status' => StorePackageGrantStatus::ACTIVE,
        'granted_at' => now(),
        'expires_at' => now()->addDays(30),
    ]);

    $this->actingAs($user)
        ->get(route('store.my-order.show', $order->uuid))
        ->assertInertia(function ($page) {
            $grant = $page->toArray()['props']['order']['items'][0]['grant'];

            expect($grant['status']['value'])->toEqual(StorePackageGrantStatus::ACTIVE->value);
            expect($grant['expires_at'])->not->toBeNull();
        });
});

test('paying still dispatches delivery alongside the notifications', function () {
    // The receipt listener must not have displaced the delivery listener.
    Notification::fake();

    markPaid(orderWithItem());

    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
});

test('the paid event carries the order', function () {
    Event::fake([StoreOrderPaid::class]);

    $order = orderWithItem();
    markPaid($order);

    Event::assertDispatched(
        StoreOrderPaid::class,
        fn (StoreOrderPaid $event) => $event->order->id === $order->id
    );
});

<?php

use App\Enums\StorePaymentStatus;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\User;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Queue::fake([ProcessStoreOrderPurchaseJob::class]);
});

/**
 * @return array{0: StoreOrder, 1: StorePayment}
 */
function orderHistoryPendingOrder(int $amount = 2000): array
{
    $order = StoreOrder::factory()->create([
        'total' => $amount,
        'amount_due' => $amount,
        'currency' => 'USD',
    ]);

    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'status' => StorePaymentStatus::PENDING,
        'amount' => $amount,
        'currency' => $order->currency,
    ]);

    return [$order, $payment];
}

function events(StoreOrder $order): array
{
    return $order->activities()->pluck('event')->all();
}

test('being paid is recorded with the amount and gateway', function () {
    [$order, $payment] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD', 'txn_123');

    $activity = $order->activities()->where('event', 'paid')->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties['amount'])->toBe(2000);
    expect($activity->properties['currency'])->toBe('USD');
    expect($activity->properties['transaction_id'])->toBe('txn_123');
});

test('the admin who marked an order paid is named', function () {
    // The question a dispute actually asks.
    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    [$order, $payment] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

    expect($order->activities()->where('event', 'paid')->first()->causer_id)->toBe($admin->id);
});

test('a gateway webhook leaves no causer rather than a wrong one', function () {
    // Nobody signed in did this, and saying so is more useful than attributing it to someone.
    [$order, $payment] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

    expect($order->activities()->where('event', 'paid')->first()->causer_id)->toBeNull();
});

test('a refund records what was returned and whether grants went with it', function () {
    [$order, $payment] = orderHistoryPendingOrder();
    $orders = app(StoreOrderService::class);
    $orders->markPaid($order, $payment, 2000, 'USD');

    $orders->refund($order->fresh(), 500);

    $activity = $order->activities()->where('event', 'partially_refunded')->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties['amount'])->toBe(500);
    expect($activity->properties['grants_revoked'])->toBeFalse();
});

test('a chargeback is recorded as such', function () {
    [$order, $payment] = orderHistoryPendingOrder();
    $orders = app(StoreOrderService::class);
    $orders->markPaid($order, $payment, 2000, 'USD');

    $orders->refund($order->fresh(), 2000, isChargeback: true);

    expect(events($order))->toContain('chargeback');
    expect($order->activities()->where('event', 'chargeback')->first()->properties['grants_revoked'])->toBeTrue();
});

test('a failed payment records the reason', function () {
    [$order, $payment] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->failPaymentAttempt($payment, 'card_declined');

    $activity = $order->activities()->where('event', 'payment_failed')->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties['reason'])->toBe('card_declined');
});

test('a cancellation records its reason', function () {
    [$order] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->cancel($order, 'Abandoned at checkout');

    $activity = $order->activities()->where('event', 'cancelled')->first();

    expect($activity)->not->toBeNull();
    expect($activity->properties['reason'])->toBe('Abandoned at checkout');
});

test('history is written to the stores own log', function () {
    // Its own log name, so a retention policy on the store's audit trail cannot sweep away
    // anybody else's records, or be swept away by theirs.
    [$order, $payment] = orderHistoryPendingOrder();

    app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

    expect(Activity::first()->log_name)->toBe(StoreOrderService::ACTIVITY_LOG);
});

test('a replayed webhook records one line not two', function () {
    // markPaid is idempotent, and so is its history: a duplicated event must not look like two
    // payments when someone reads the order back.
    [$order, $payment] = orderHistoryPendingOrder();
    $orders = app(StoreOrderService::class);

    $orders->markPaid($order, $payment, 2000, 'USD');
    $orders->markPaid($order->fresh(), $payment->fresh(), 2000, 'USD');

    expect($order->activities()->where('event', 'paid')->count())->toBe(1);
});

test('the admin order page shows the history oldest first', function () {
    $admin = User::whereId(1)->first();
    [$order, $payment] = orderHistoryPendingOrder();
    $orders = app(StoreOrderService::class);
    $orders->markPaid($order, $payment, 2000, 'USD');
    $orders->refund($order->fresh(), 2000);

    $this->actingAs($admin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            $timeline = $page->toArray()['props']['timeline'];

            // Placed is derived from the order, so it is there even before anything is logged.
            expect($timeline[0]['event'])->toBe('placed');
            expect($timeline[1]['event'])->toBe('paid');
            expect($timeline[2]['event'])->toBe('refunded');
            // Money is formatted server-side, in the order's currency.
            expect($timeline[2]['detail'])->toBe('$20.00');
        });
});

test('a brand new order still has a timeline', function () {
    [$order] = orderHistoryPendingOrder();

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page
            ->has('timeline', 1)
            ->where('timeline.0.event', 'placed')
        );
});

test('resending delivery is recorded against the admin who did it', function () {
    $admin = User::whereId(1)->first();
    [$order, $payment] = orderHistoryPendingOrder();
    app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

    $this->actingAs($admin)->post(route('admin.store.order.resend', $order->uuid));

    // Nothing to re-send on an order with no deliveries, so nothing is claimed to have happened.
    expect(events($order->fresh()))->not->toContain('delivery_resent');
});

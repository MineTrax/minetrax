<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\StoreBan;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\User;
use App\Notifications\StoreChargebackStaffNotification;
use App\Notifications\StoreOrderRefundedNotification;
use App\Notifications\StorePaymentFailedNotification;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
    Notification::fake();
});

function refundNoticePaidOrder(array $attributes = []): StoreOrder
{
    $order = StoreOrder::factory()->paid()->create(array_merge([
        'total' => 2000,
        'amount_due' => 2000,
        'ip_address' => '203.0.113.9',
        'email' => 'buyer@example.com',
    ], $attributes));

    StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'status' => StorePaymentStatus::COMPLETED,
        'amount' => 2000,
        'currency' => $order->currency,
    ]);

    return $order->fresh();
}

function setAutoBan(bool $enabled): void
{
    $settings = app(StoreSettings::class);
    $settings->auto_ban_on_chargeback = $enabled;
    $settings->save();
}

test('a refund notifies the buyer with the amount refunded', function () {
    $user = User::factory()->create();
    $order = refundNoticePaidOrder(['user_id' => $user->id]);

    app(StoreOrderService::class)->refund($order, 500);

    Notification::assertSentTo($user, StoreOrderRefundedNotification::class, function ($notification) {
        // The amount refunded, not the order total: a partial refund must not claim more.
        return $notification->amountMinor === 500;
    });
});

test('a guest refund goes to the address given at checkout', function () {
    $order = refundNoticePaidOrder(['user_id' => null, 'email' => 'guest@example.com']);

    app(StoreOrderService::class)->refund($order, 2000);

    Notification::assertSentOnDemand(StoreOrderRefundedNotification::class);
});

test('a guest with no email is simply not notified', function () {
    $order = refundNoticePaidOrder(['user_id' => null, 'email' => null]);

    app(StoreOrderService::class)->refund($order, 2000);

    Notification::assertNothingSent();
});

test('a chargeback does not email the buyer', function () {
    // The buyer raised it with their own bank, so telling them about it reads as an accusation.
    $user = User::factory()->create();
    $order = refundNoticePaidOrder(['user_id' => $user->id]);

    app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

    Notification::assertNotSentTo($user, StoreOrderRefundedNotification::class);
});

test('a chargeback notifies staff', function () {
    $order = refundNoticePaidOrder();

    app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

    Notification::assertSentTo(
        User::whereId(1)->first(),
        StoreChargebackStaffNotification::class
    );
});

test('a chargeback raises a ban when the setting is on', function () {
    setAutoBan(true);
    $user = User::factory()->create();
    $order = refundNoticePaidOrder(['user_id' => $user->id]);

    app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

    $this->assertDatabaseHas('store_bans', [
        'user_id' => $user->id,
        'player_uuid' => $order->player_uuid,
        'ip_address' => '203.0.113.9',
        'email' => 'buyer@example.com',
        'is_automatic' => true,
    ]);
});

test('no ban is raised when the setting is off', function () {
    setAutoBan(false);
    $order = refundNoticePaidOrder();

    app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

    $this->assertDatabaseCount('store_bans', 0);
});

test('an ordinary refund never bans anyone', function () {
    setAutoBan(true);
    $order = refundNoticePaidOrder();

    app(StoreOrderService::class)->refund($order, 2000);

    $this->assertDatabaseCount('store_bans', 0);
});

test('a second chargeback does not stack another ban', function () {
    setAutoBan(true);
    $user = User::factory()->create();

    $first = refundNoticePaidOrder(['user_id' => $user->id]);
    app(StoreOrderService::class)->refund($first, 2000, isChargeback: true);

    $second = refundNoticePaidOrder(['user_id' => $user->id]);
    app(StoreOrderService::class)->refund($second, 2000, isChargeback: true);

    expect(StoreBan::count())->toBe(1);
});

test('the staff notification says whether a ban was raised', function () {
    setAutoBan(true);
    $order = refundNoticePaidOrder();

    app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

    Notification::assertSentTo(
        User::whereId(1)->first(),
        StoreChargebackStaffNotification::class,
        fn ($notification) => $notification->wasBanned === true
    );
});

test('a failed charge tells the buyer the order is still payable', function () {
    $user = User::factory()->create();
    $order = StoreOrder::factory()->create([
        'user_id' => $user->id,
        'status' => StoreOrderStatus::PENDING,
        'total' => 2000,
        'amount_due' => 2000,
    ]);
    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'status' => StorePaymentStatus::PENDING,
        'amount' => 2000,
        'currency' => $order->currency,
    ]);

    app(StoreOrderService::class)->failPaymentAttempt($payment, 'card_declined');

    Notification::assertSentTo($user, StorePaymentFailedNotification::class);

    // The order is untouched, which is the whole point: the buyer can pay it another way.
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('an amount mismatch at the gateway also notifies the buyer', function () {
    // markPaid fails the payment rather than the order when the gateway reports the wrong
    // amount, and that failure is worth an email — nothing else would tell the buyer.
    $user = User::factory()->create();
    $order = StoreOrder::factory()->create([
        'user_id' => $user->id,
        'status' => StoreOrderStatus::PENDING,
        'total' => 2000,
        'amount_due' => 2000,
    ]);
    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'status' => StorePaymentStatus::PENDING,
        'amount' => 2000,
        'currency' => $order->currency,
    ]);

    $marked = app(StoreOrderService::class)->markPaid($order, $payment, 1, $order->currency);

    expect($marked)->toBeFalse();
    Notification::assertSentTo($user, StorePaymentFailedNotification::class);
});

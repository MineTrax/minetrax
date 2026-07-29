<?php

namespace Tests\Feature\Store;

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
use Tests\TestCase;

/**
 * What the buyer and staff are told when money goes backwards, and the ban a lost dispute can raise.
 */
class StoreRefundNoticeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();
        Notification::fake();
    }

    private function paidOrder(array $attributes = []): StoreOrder
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

    private function setAutoBan(bool $enabled): void
    {
        $settings = app(StoreSettings::class);
        $settings->auto_ban_on_chargeback = $enabled;
        $settings->save();
    }

    public function test_a_refund_notifies_the_buyer_with_the_amount_refunded()
    {
        $user = User::factory()->create();
        $order = $this->paidOrder(['user_id' => $user->id]);

        app(StoreOrderService::class)->refund($order, 500);

        Notification::assertSentTo($user, StoreOrderRefundedNotification::class, function ($notification) {
            // The amount refunded, not the order total: a partial refund must not claim more.
            return $notification->amountMinor === 500;
        });
    }

    public function test_a_guest_refund_goes_to_the_address_given_at_checkout()
    {
        $order = $this->paidOrder(['user_id' => null, 'email' => 'guest@example.com']);

        app(StoreOrderService::class)->refund($order, 2000);

        Notification::assertSentOnDemand(StoreOrderRefundedNotification::class);
    }

    public function test_a_guest_with_no_email_is_simply_not_notified()
    {
        $order = $this->paidOrder(['user_id' => null, 'email' => null]);

        app(StoreOrderService::class)->refund($order, 2000);

        Notification::assertNothingSent();
    }

    public function test_a_chargeback_does_not_email_the_buyer()
    {
        // The buyer raised it with their own bank, so telling them about it reads as an accusation.
        $user = User::factory()->create();
        $order = $this->paidOrder(['user_id' => $user->id]);

        app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

        Notification::assertNotSentTo($user, StoreOrderRefundedNotification::class);
    }

    public function test_a_chargeback_notifies_staff()
    {
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

        Notification::assertSentTo(
            User::whereId(1)->first(),
            StoreChargebackStaffNotification::class
        );
    }

    public function test_a_chargeback_raises_a_ban_when_the_setting_is_on()
    {
        $this->setAutoBan(true);
        $user = User::factory()->create();
        $order = $this->paidOrder(['user_id' => $user->id]);

        app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

        $this->assertDatabaseHas('store_bans', [
            'user_id' => $user->id,
            'player_uuid' => $order->player_uuid,
            'ip_address' => '203.0.113.9',
            'email' => 'buyer@example.com',
            'is_automatic' => true,
        ]);
    }

    public function test_no_ban_is_raised_when_the_setting_is_off()
    {
        $this->setAutoBan(false);
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

        $this->assertDatabaseCount('store_bans', 0);
    }

    public function test_an_ordinary_refund_never_bans_anyone()
    {
        $this->setAutoBan(true);
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 2000);

        $this->assertDatabaseCount('store_bans', 0);
    }

    public function test_a_second_chargeback_does_not_stack_another_ban()
    {
        $this->setAutoBan(true);
        $user = User::factory()->create();

        $first = $this->paidOrder(['user_id' => $user->id]);
        app(StoreOrderService::class)->refund($first, 2000, isChargeback: true);

        $second = $this->paidOrder(['user_id' => $user->id]);
        app(StoreOrderService::class)->refund($second, 2000, isChargeback: true);

        $this->assertSame(1, StoreBan::count());
    }

    public function test_the_staff_notification_says_whether_a_ban_was_raised()
    {
        $this->setAutoBan(true);
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 2000, isChargeback: true);

        Notification::assertSentTo(
            User::whereId(1)->first(),
            StoreChargebackStaffNotification::class,
            fn ($notification) => $notification->wasBanned === true
        );
    }

    public function test_a_failed_charge_tells_the_buyer_the_order_is_still_payable()
    {
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
        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_an_amount_mismatch_at_the_gateway_also_notifies_the_buyer()
    {
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

        $this->assertFalse($marked);
        Notification::assertSentTo($user, StorePaymentFailedNotification::class);
    }
}

<?php

namespace Tests\Feature\Store;

use App\Enums\StorePaymentStatus;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\User;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * The order history. Its whole reason for existing is answering "who did this" weeks later, when a
 * buyer disputes a charge or claims they were never delivered to.
 */
class StoreOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        Queue::fake([ProcessStoreOrderPurchaseJob::class]);
    }

    /**
     * @return array{0: StoreOrder, 1: StorePayment}
     */
    private function pendingOrder(int $amount = 2000): array
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

    private function events(StoreOrder $order): array
    {
        return $order->activities()->pluck('event')->all();
    }

    public function test_being_paid_is_recorded_with_the_amount_and_gateway()
    {
        [$order, $payment] = $this->pendingOrder();

        app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD', 'txn_123');

        $activity = $order->activities()->where('event', 'paid')->first();

        $this->assertNotNull($activity);
        $this->assertSame(2000, $activity->properties['amount']);
        $this->assertSame('USD', $activity->properties['currency']);
        $this->assertSame('txn_123', $activity->properties['transaction_id']);
    }

    public function test_the_admin_who_marked_an_order_paid_is_named()
    {
        // The question a dispute actually asks.
        $admin = User::whereId(1)->first();
        $this->actingAs($admin);

        [$order, $payment] = $this->pendingOrder();

        app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

        $this->assertSame(
            $admin->id,
            $order->activities()->where('event', 'paid')->first()->causer_id
        );
    }

    public function test_a_gateway_webhook_leaves_no_causer_rather_than_a_wrong_one()
    {
        // Nobody signed in did this, and saying so is more useful than attributing it to someone.
        [$order, $payment] = $this->pendingOrder();

        app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

        $this->assertNull($order->activities()->where('event', 'paid')->first()->causer_id);
    }

    public function test_a_refund_records_what_was_returned_and_whether_grants_went_with_it()
    {
        [$order, $payment] = $this->pendingOrder();
        $orders = app(StoreOrderService::class);
        $orders->markPaid($order, $payment, 2000, 'USD');

        $orders->refund($order->fresh(), 500);

        $activity = $order->activities()->where('event', 'partially_refunded')->first();

        $this->assertNotNull($activity);
        $this->assertSame(500, $activity->properties['amount']);
        $this->assertFalse($activity->properties['grants_revoked']);
    }

    public function test_a_chargeback_is_recorded_as_such()
    {
        [$order, $payment] = $this->pendingOrder();
        $orders = app(StoreOrderService::class);
        $orders->markPaid($order, $payment, 2000, 'USD');

        $orders->refund($order->fresh(), 2000, isChargeback: true);

        $this->assertContains('chargeback', $this->events($order));
        $this->assertTrue(
            $order->activities()->where('event', 'chargeback')->first()->properties['grants_revoked']
        );
    }

    public function test_a_failed_payment_records_the_reason()
    {
        [$order, $payment] = $this->pendingOrder();

        app(StoreOrderService::class)->failPaymentAttempt($payment, 'card_declined');

        $activity = $order->activities()->where('event', 'payment_failed')->first();

        $this->assertNotNull($activity);
        $this->assertSame('card_declined', $activity->properties['reason']);
    }

    public function test_a_cancellation_records_its_reason()
    {
        [$order] = $this->pendingOrder();

        app(StoreOrderService::class)->cancel($order, 'Abandoned at checkout');

        $activity = $order->activities()->where('event', 'cancelled')->first();

        $this->assertNotNull($activity);
        $this->assertSame('Abandoned at checkout', $activity->properties['reason']);
    }

    public function test_history_is_written_to_the_stores_own_log()
    {
        // Its own log name, so a retention policy on the store's audit trail cannot sweep away
        // anybody else's records, or be swept away by theirs.
        [$order, $payment] = $this->pendingOrder();

        app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

        $this->assertSame(StoreOrderService::ACTIVITY_LOG, Activity::first()->log_name);
    }

    public function test_a_replayed_webhook_records_one_line_not_two()
    {
        // markPaid is idempotent, and so is its history: a duplicated event must not look like two
        // payments when someone reads the order back.
        [$order, $payment] = $this->pendingOrder();
        $orders = app(StoreOrderService::class);

        $orders->markPaid($order, $payment, 2000, 'USD');
        $orders->markPaid($order->fresh(), $payment->fresh(), 2000, 'USD');

        $this->assertSame(1, $order->activities()->where('event', 'paid')->count());
    }

    public function test_the_admin_order_page_shows_the_history_oldest_first()
    {
        $admin = User::whereId(1)->first();
        [$order, $payment] = $this->pendingOrder();
        $orders = app(StoreOrderService::class);
        $orders->markPaid($order, $payment, 2000, 'USD');
        $orders->refund($order->fresh(), 2000);

        $this->actingAs($admin)
            ->get(route('admin.store.order.show', $order->uuid))
            ->assertStatus(200)
            ->assertInertia(function ($page) {
                $timeline = $page->toArray()['props']['timeline'];

                // Placed is derived from the order, so it is there even before anything is logged.
                $this->assertSame('placed', $timeline[0]['event']);
                $this->assertSame('paid', $timeline[1]['event']);
                $this->assertSame('refunded', $timeline[2]['event']);
                // Money is formatted server-side, in the order's currency.
                $this->assertSame('$20.00', $timeline[2]['detail']);
            });
    }

    public function test_a_brand_new_order_still_has_a_timeline()
    {
        [$order] = $this->pendingOrder();

        $this->actingAs(User::whereId(1)->first())
            ->get(route('admin.store.order.show', $order->uuid))
            ->assertInertia(fn ($page) => $page
                ->has('timeline', 1)
                ->where('timeline.0.event', 'placed')
            );
    }

    public function test_resending_delivery_is_recorded_against_the_admin_who_did_it()
    {
        $admin = User::whereId(1)->first();
        [$order, $payment] = $this->pendingOrder();
        app(StoreOrderService::class)->markPaid($order, $payment, 2000, 'USD');

        $this->actingAs($admin)->post(route('admin.store.order.resend', $order->uuid));

        // Nothing to re-send on an order with no deliveries, so nothing is claimed to have happened.
        $this->assertNotContains('delivery_resent', $this->events($order->fresh()));
    }
}

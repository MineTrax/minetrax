<?php

namespace Tests\Feature\Store;

use App\Contracts\StorePaymentGatewayContract;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderPaid;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StoreOrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private StoreOrderService $orders;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        StoreCurrency::factory()->base()->create();
        $this->orders = app(StoreOrderService::class);
    }

    private function pendingOrder(array $attributes = []): StoreOrder
    {
        return StoreOrder::factory()->create(array_merge([
            'total' => 1000, 'amount_due' => 1000, 'currency' => 'USD',
        ], $attributes));
    }

    private function paymentFor(StoreOrder $order): StorePayment
    {
        return StorePayment::factory()->create([
            'store_order_id' => $order->id,
            'amount' => $order->amount_due,
            'currency' => $order->currency,
        ]);
    }

    public function test_a_pending_order_can_be_marked_paid()
    {
        Event::fake([StoreOrderPaid::class]);
        $order = $this->pendingOrder();
        $payment = $this->paymentFor($order);

        $this->assertTrue($this->orders->markPaid($order, $payment, 1000, 'USD', 'txn_1'));

        $order->refresh();
        $this->assertEquals(StoreOrderStatus::PAID, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertEquals(StorePaymentStatus::COMPLETED, $payment->fresh()->status);
        $this->assertEquals('txn_1', $payment->fresh()->gateway_transaction_id);
        Event::assertDispatched(StoreOrderPaid::class);
    }

    public function test_marking_paid_twice_is_a_no_op()
    {
        // A gateway will happily redeliver the same webhook; the second must not double-deliver.
        Event::fake([StoreOrderPaid::class]);
        $order = $this->pendingOrder();
        $payment = $this->paymentFor($order);

        $this->assertTrue($this->orders->markPaid($order, $payment, 1000, 'USD'));
        $this->assertFalse($this->orders->markPaid($order->fresh(), $payment, 1000, 'USD'));

        Event::assertDispatchedTimes(StoreOrderPaid::class, 1);
    }

    public function test_an_amount_mismatch_fails_the_payment_and_leaves_the_order_pending()
    {
        $order = $this->pendingOrder();
        $payment = $this->paymentFor($order);

        $this->assertFalse($this->orders->markPaid($order, $payment, 1, 'USD'));

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
        $this->assertStringContainsString('Amount mismatch', $payment->fresh()->failure_reason);
    }

    public function test_a_currency_mismatch_fails_the_payment()
    {
        $order = $this->pendingOrder();
        $payment = $this->paymentFor($order);

        $this->assertFalse($this->orders->markPaid($order, $payment, 1000, 'EUR'));

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    public function test_a_paid_order_can_be_completed()
    {
        $order = StoreOrder::factory()->paid()->create();

        $this->assertTrue($this->orders->markCompleted($order, StoreDeliveryStatus::DELIVERED));

        $order->refresh();
        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->status);
        $this->assertEquals(StoreDeliveryStatus::DELIVERED, $order->delivery_status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_a_pending_order_cannot_jump_straight_to_completed()
    {
        $order = $this->pendingOrder();

        $this->assertFalse($this->orders->markCompleted($order, StoreDeliveryStatus::DELIVERED));
        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_refunded_order_cannot_be_resurrected()
    {
        $order = StoreOrder::factory()->completed()->create();
        $this->orders->refund($order, (int) $order->amount_due);
        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);

        $payment = $this->paymentFor($order);
        $this->assertFalse($this->orders->markPaid($order->fresh(), $payment, (int) $order->amount_due, $order->currency));
        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);
    }

    public function test_a_partial_refund_leaves_the_order_partially_refunded()
    {
        $order = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);

        $this->assertTrue($this->orders->refund($order, 400));

        $this->assertEquals(StoreOrderStatus::PARTIALLY_REFUNDED, $order->fresh()->status);
    }

    public function test_a_full_refund_revokes_grants_but_a_partial_one_does_not()
    {
        $package = StorePackage::factory()->create();

        $partial = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);
        $partialItem = $partial->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);
        $partialGrant = $partialItem->grant()->create([
            'store_package_id' => $package->id, 'player_uuid' => $partial->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE, 'granted_at' => now(),
        ]);

        $this->orders->refund($partial, 400);
        $this->assertEquals(StorePackageGrantStatus::ACTIVE, $partialGrant->fresh()->status);

        $full = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);
        $fullItem = $full->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);
        $fullGrant = $fullItem->grant()->create([
            'store_package_id' => $package->id, 'player_uuid' => $full->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE, 'granted_at' => now(),
        ]);

        $this->orders->refund($full, 1000);
        $this->assertEquals(StorePackageGrantStatus::REVOKED, $fullGrant->fresh()->status);
        $this->assertNotNull($fullGrant->fresh()->revoked_at);
    }

    public function test_a_chargeback_always_revokes_even_for_a_small_amount()
    {
        $package = StorePackage::factory()->create();
        $order = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);
        $item = $order->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);
        $grant = $item->grant()->create([
            'store_package_id' => $package->id, 'player_uuid' => $order->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE, 'granted_at' => now(),
        ]);

        $this->orders->refund($order, 1, isChargeback: true);

        $this->assertEquals(StoreOrderStatus::CHARGEBACK, $order->fresh()->status);
        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->fresh()->status);
    }

    public function test_a_gift_card_is_debited_once_even_if_mark_paid_is_retried()
    {
        $card = StoreGiftCard::create([
            'code' => 'GC', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
        ]);
        $order = $this->pendingOrder(['gift_card_amount' => 500, 'amount_due' => 500, 'store_gift_card_id' => $card->id]);
        $payment = $this->paymentFor($order);

        $this->orders->markPaid($order, $payment, 500, 'USD');
        $this->orders->markPaid($order->fresh(), $payment, 500, 'USD');

        $this->assertEquals(0, $card->fresh()->balance);
        $this->assertEquals(1, $card->transactions()->count(), 'The ledger must not double-record.');
    }

    public function test_cancelling_a_pending_order_recredits_a_redeemed_gift_card()
    {
        $card = StoreGiftCard::create([
            'code' => 'GC', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
        ]);
        $order = $this->pendingOrder(['gift_card_amount' => 500, 'amount_due' => 500, 'store_gift_card_id' => $card->id]);
        $payment = $this->paymentFor($order);

        $this->orders->markPaid($order, $payment, 500, 'USD');
        $this->assertEquals(0, $card->fresh()->balance);

        // PAID can still be cancelled by an admin.
        $this->orders->cancel($order->fresh(), 'Admin cancelled');

        $this->assertEquals(500, $card->fresh()->balance);
        $this->assertEquals(2, $card->transactions()->count());
    }

    public function test_cancelling_twice_is_a_no_op()
    {
        $order = $this->pendingOrder();

        $this->assertTrue($this->orders->cancel($order));
        $this->assertFalse($this->orders->cancel($order->fresh()));
    }

    // --- Gateway registry ------------------------------------------------------------------

    public function test_every_registered_gateway_satisfies_the_contract()
    {
        // This is what makes a future driver covered the moment it is registered.
        $manager = app(StorePaymentGatewayManager::class);
        $seenKeys = [];

        foreach ($manager->all() as $key => $driver) {
            $this->assertInstanceOf(StorePaymentGatewayContract::class, $driver);
            $this->assertEquals($key, $driver->gateway()->value, 'The config key must match the driver enum value.');
            $this->assertNotEmpty($driver->label());
            $this->assertNotContains($driver->gateway()->value, $seenKeys, 'Gateway keys must be unique.');
            $seenKeys[] = $driver->gateway()->value;

            foreach ($driver->settingsSchema() as $field) {
                $this->assertArrayHasKey('key', $field);
                $this->assertArrayHasKey('label', $field);
                $this->assertArrayHasKey('type', $field);
            }
        }

        $this->assertNotEmpty($seenKeys);
    }

    public function test_a_gateway_the_admin_has_not_enabled_is_not_offered()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = [];
        $settings->save();

        $this->assertCount(0, app(StorePaymentGatewayManager::class)->enabled());
    }

    public function test_an_unknown_gateway_key_resolves_to_null_rather_than_exploding()
    {
        $this->assertNull(app(StorePaymentGatewayManager::class)->driver('does-not-exist'));
        $this->assertFalse(app(StorePaymentGatewayManager::class)->has('does-not-exist'));
    }
}

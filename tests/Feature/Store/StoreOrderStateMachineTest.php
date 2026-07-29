<?php

use App\Contracts\StorePaymentGatewayContract;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderPaid;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // Isolate transitions from fulfilment. markPaid fires StoreOrderPaid, whose listener runs
    // ProcessStoreOrderPurchaseJob synchronously on the sync queue and would advance the order
    // straight to COMPLETED. Delivery has its own test file.
    Queue::fake([ProcessStoreOrderPurchaseJob::class]);

    $this->orders = app(StoreOrderService::class);
});

function orderStateMachinePendingOrder(array $attributes = []): StoreOrder
{
    return StoreOrder::factory()->create(array_merge([
        'total' => 1000, 'amount_due' => 1000, 'currency' => 'USD',
    ], $attributes));
}

function paymentFor(StoreOrder $order): StorePayment
{
    return StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => $order->amount_due,
        'currency' => $order->currency,
    ]);
}

test('a pending order can be marked paid', function () {
    Event::fake([StoreOrderPaid::class]);
    $order = orderStateMachinePendingOrder();
    $payment = paymentFor($order);

    expect($this->orders->markPaid($order, $payment, 1000, 'USD', 'txn_1'))->toBeTrue();

    $order->refresh();
    expect($order->status)->toEqual(StoreOrderStatus::PAID);
    expect($order->paid_at)->not->toBeNull();
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::COMPLETED);
    expect($payment->fresh()->gateway_transaction_id)->toEqual('txn_1');
    Event::assertDispatched(StoreOrderPaid::class);
});

test('marking paid twice is a no op', function () {
    // A gateway will happily redeliver the same webhook; the second must not double-deliver.
    Event::fake([StoreOrderPaid::class]);
    $order = orderStateMachinePendingOrder();
    $payment = paymentFor($order);

    expect($this->orders->markPaid($order, $payment, 1000, 'USD'))->toBeTrue();
    expect($this->orders->markPaid($order->fresh(), $payment, 1000, 'USD'))->toBeFalse();

    Event::assertDispatchedTimes(StoreOrderPaid::class, 1);
});

test('an amount mismatch fails the payment and leaves the order pending', function () {
    $order = orderStateMachinePendingOrder();
    $payment = paymentFor($order);

    expect($this->orders->markPaid($order, $payment, 1, 'USD'))->toBeFalse();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
    $this->assertStringContainsString('Amount mismatch', $payment->fresh()->failure_reason);
});

test('a currency mismatch fails the payment', function () {
    $order = orderStateMachinePendingOrder();
    $payment = paymentFor($order);

    expect($this->orders->markPaid($order, $payment, 1000, 'EUR'))->toBeFalse();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('a paid order can be completed', function () {
    $order = StoreOrder::factory()->paid()->create();

    expect($this->orders->markCompleted($order, StoreDeliveryStatus::DELIVERED))->toBeTrue();

    $order->refresh();
    expect($order->status)->toEqual(StoreOrderStatus::COMPLETED);
    expect($order->delivery_status)->toEqual(StoreDeliveryStatus::DELIVERED);
    expect($order->completed_at)->not->toBeNull();
});

test('a pending order cannot jump straight to completed', function () {
    $order = orderStateMachinePendingOrder();

    expect($this->orders->markCompleted($order, StoreDeliveryStatus::DELIVERED))->toBeFalse();
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('a refunded order cannot be resurrected', function () {
    $order = StoreOrder::factory()->completed()->create();
    $this->orders->refund($order, (int) $order->amount_due);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);

    $payment = paymentFor($order);
    expect($this->orders->markPaid($order->fresh(), $payment, (int) $order->amount_due, $order->currency))->toBeFalse();
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);
});

test('a partial refund leaves the order partially refunded', function () {
    $order = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);

    expect($this->orders->refund($order, 400))->toBeTrue();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
});

test('a full refund revokes grants but a partial one does not', function () {
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
    expect($partialGrant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE);

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
    expect($fullGrant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
    expect($fullGrant->fresh()->revoked_at)->not->toBeNull();
});

test('a paid order accepts a partial refund before delivery completes', function () {
    // Delivery usually moves an order to COMPLETED within seconds, but a stalled queue worker
    // leaves it PAID. Refusing the refund there would mean the gateway had returned money the
    // site never recorded.
    $order = StoreOrder::factory()->paid()->create(['total' => 1000, 'amount_due' => 1000]);

    expect($this->orders->refund($order, 400))->toBeTrue();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
});

test('a paid order accepts a chargeback before delivery completes', function () {
    $order = StoreOrder::factory()->paid()->create(['total' => 1000, 'amount_due' => 1000]);

    expect($this->orders->refund($order, 1000, isChargeback: true))->toBeTrue();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CHARGEBACK);
});

test('a chargeback always revokes even for a small amount', function () {
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

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CHARGEBACK);
    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
});

test('a gift card is debited once even if mark paid is retried', function () {
    $card = StoreGiftCard::create([
        'code' => 'GC', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
    ]);
    $order = orderStateMachinePendingOrder(['gift_card_amount' => 500, 'amount_due' => 500, 'store_gift_card_id' => $card->id]);
    $payment = paymentFor($order);

    $this->orders->markPaid($order, $payment, 500, 'USD');
    $this->orders->markPaid($order->fresh(), $payment, 500, 'USD');

    expect($card->fresh()->balance)->toEqual(0);
    expect($card->transactions()->count())->toEqual(1, 'The ledger must not double-record.');
});

test('cancelling a pending order recredits a redeemed gift card', function () {
    $card = StoreGiftCard::create([
        'code' => 'GC', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
    ]);
    $order = orderStateMachinePendingOrder(['gift_card_amount' => 500, 'amount_due' => 500, 'store_gift_card_id' => $card->id]);
    $payment = paymentFor($order);

    $this->orders->markPaid($order, $payment, 500, 'USD');
    expect($card->fresh()->balance)->toEqual(0);

    // PAID can still be cancelled by an admin.
    $this->orders->cancel($order->fresh(), 'Admin cancelled');

    expect($card->fresh()->balance)->toEqual(500);
    expect($card->transactions()->count())->toEqual(2);
});

test('cancelling twice is a no op', function () {
    $order = orderStateMachinePendingOrder();

    expect($this->orders->cancel($order))->toBeTrue();
    expect($this->orders->cancel($order->fresh()))->toBeFalse();
});

test('every registered gateway satisfies the contract', function () {
    // This is what makes a future driver covered the moment it is registered.
    $manager = app(StorePaymentGatewayManager::class);
    $seenKeys = [];

    foreach ($manager->all() as $key => $driver) {
        expect($driver)->toBeInstanceOf(StorePaymentGatewayContract::class);
        expect($driver->gateway()->value)->toEqual($key, 'The config key must match the driver enum value.');
        expect($driver->label())->not->toBeEmpty();
        expect($seenKeys)->not->toContain($driver->gateway()->value, 'Gateway keys must be unique.');
        $seenKeys[] = $driver->gateway()->value;

        foreach ($driver->settingsSchema() as $field) {
            expect($field)->toHaveKey('key');
            expect($field)->toHaveKey('label');
            expect($field)->toHaveKey('type');
        }
    }

    expect($seenKeys)->not->toBeEmpty();
});

test('a gateway the admin has not enabled is not offered', function () {
    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = [];
    $settings->save();

    expect(app(StorePaymentGatewayManager::class)->enabled())->toHaveCount(0);
});

test('an unknown gateway key resolves to null rather than exploding', function () {
    expect(app(StorePaymentGatewayManager::class)->driver('does-not-exist'))->toBeNull();
    expect(app(StorePaymentGatewayManager::class)->has('does-not-exist'))->toBeFalse();
});

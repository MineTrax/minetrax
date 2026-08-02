<?php

use App\Enums\CommandQueueStatus;
use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentRefundType;
use App\Enums\StorePaymentStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ExpireStalePendingStoreOrdersJob;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Models\User;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Queue::fake([ProcessStoreOrderPurchaseJob::class, RunCommandQueueJob::class]);

    $this->superadmin = User::whereId(1)->first();
});

/**
 * A staff user carrying exactly the listed store permissions, so each action's own permission
 * can be tested rather than the blanket superadmin bypass.
 */
function staffWith(array $permissions): User
{
    $role = Role::create([
        'name' => 'store-staff-'.uniqid(),
        'display_name' => 'Store Staff',
        'guard_name' => 'sanctum',
        'is_staff' => true,
        'weight' => 1,
    ]);
    $role->givePermissionTo($permissions);

    return tap(User::factory()->create())->assignRole($role);
}

function orderAdminPaidOrder(int $amount = 2000): array
{
    $order = StoreOrder::factory()->completed()->create([
        'total' => $amount, 'amount_due' => $amount, 'currency' => 'USD',
        'gateway' => StorePaymentGateway::STRIPE,
    ]);

    $payment = StorePayment::factory()->completed()->create([
        'store_order_id' => $order->id,
        'gateway' => StorePaymentGateway::STRIPE,
        'gateway_transaction_id' => 'pi_'.$order->id,
        'amount' => $amount,
        'currency' => 'USD',
    ]);

    return [$order, $payment];
}

test('a guest is redirected to login', function () {
    $this->get(route('admin.store.order.index'))->assertRedirect(route('login'));
});

test('a non staff user cannot reach the listing', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.order.index'))
        ->assertStatus(302);
});

test('staff without read permission are forbidden', function () {
    $this->actingAs(staffWith(['read store_packages']))
        ->get(route('admin.store.order.index'))
        ->assertForbidden();
});

test('the listing is hidden when the store is disabled', function () {
    config(['store.enabled' => false]);

    $this->actingAs(staffWith(['read store_orders']))
        ->get(route('admin.store.order.index'))
        ->assertForbidden();
});

test('the listing renders with orders', function () {
    StoreOrder::factory()->count(3)->create();

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreOrder/IndexStoreOrder')
            ->has('orders.data', 3)
        );
});

test('each row carries a total formatted in its own currency', function () {
    // Two orders in different currencies must not be formatted with one shared symbol.
    StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'symbol' => '¥', 'is_base' => false, 'rate_to_base' => 150]);

    StoreOrder::factory()->create(['currency' => 'USD', 'total' => 1999]);
    StoreOrder::factory()->create(['currency' => 'JPY', 'total' => 1000]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.index'))
        ->assertInertia(function ($page) {
            $totals = collect($page->toArray()['props']['orders']['data'])->pluck('total_formatted');

            expect($totals->contains('$19.99'))->toBeTrue();
            // Zero-decimal: ¥1000 is a thousand yen, not ten.
            expect($totals->contains('¥1,000'))->toBeTrue();
        });
});

test('the listing can be filtered by player username', function () {
    StoreOrder::factory()->create(['player_username' => 'Notch']);
    StoreOrder::factory()->create(['player_username' => 'Herobrine']);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.index', ['filter' => ['q' => 'Notch']]))
        ->assertInertia(fn ($page) => $page->has('orders.data', 1));
});

test('the detail page renders', function () {
    [$order] = orderAdminPaidOrder();

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreOrder/ShowStoreOrder')
            ->has('order')
            ->has('money.total')
            // Not `permissions`: that name belongs to the globally shared array of the user's
            // permission names, and a page prop of the same name replaces it and breaks
            // useAuthorizable. Asserting the real name stops the rename silently reverting.
            ->has('orderPermissions.refund')
        );
});

test('item amounts are formatted rather than sent as minor units', function () {
    // Rendered raw, a $1.49 crate key read "149 USD" on the page.
    [$order] = orderAdminPaidOrder(149);
    $package = StorePackage::factory()->create();
    $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 149,
        'unit_price' => 149,
        'total' => 149,
    ]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page
            ->where('order.items.0.total_formatted', '$1.49')
            ->where('order.items.0.unit_price_formatted', '$1.49')
            ->where('order.payments.0.amount_formatted', '$1.49')
        );
});

test('a zero decimal currency is not divided when formatting an item', function () {
    // ¥1490 is 1490 minor units. Dividing by a hundred in the template would show ¥14.90 for an
    // amount that has no minor unit at all.
    StoreCurrency::factory()->zeroDecimal()->create();

    $order = StoreOrder::factory()->completed()->create([
        'total' => 1490, 'amount_due' => 1490, 'currency' => 'JPY', 'exchange_rate' => 150,
    ]);
    $package = StorePackage::factory()->create();
    $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 1490,
        'unit_price' => 1490,
        'total' => 1490,
    ]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page
            ->where('order.items.0.total_formatted', '¥1,490')
        );
});

test('refund amounts are formatted too', function () {
    [$order, $payment] = orderAdminPaidOrder(2000);
    $payment->refunds()->create([
        'type' => StorePaymentRefundType::REFUND,
        'amount' => 500,
        'currency' => 'USD',
        'reason' => 'Goodwill',
    ]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page
            ->where('order.payments.0.refunds.0.amount_formatted', '$5.00')
        );
});

test('a delivery deferred past the attention window is flagged', function () {
    config(['store.deferred_attention_days' => 3]);

    [$order] = orderAdminPaidOrder();
    $package = StorePackage::factory()->create();
    $item = $order->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 2000, 'unit_price' => 2000, 'total' => 2000,
    ]);

    $queue = CommandQueue::create([
        'server_id' => Server::factory()->create()->id,
        'parsed_command' => 'give Notch diamond 1',
        'config' => ['is_player_online_required' => true],
        'status' => CommandQueueStatus::DEFERRED,
        'max_attempts' => 3,
    ]);

    $delivery = StoreOrderDelivery::create([
        'store_order_id' => $order->id,
        'store_order_item_id' => $item->id,
        'store_command_id' => null,
        'server_id' => $queue->server_id,
        'command_queue_id' => $queue->id,
        'trigger' => StoreCommandTrigger::PURCHASE,
        'parsed_command' => 'give Notch diamond 1',
        'repeat_index' => 0,
    ]);
    $delivery->forceFill(['created_at' => now()->subDays(5)])->save();

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(fn ($page) => $page->has('stuckDeliveries', 1));
});

test('an admin can mark a pending order paid', function () {
    $order = StoreOrder::factory()->create(['total' => 500, 'amount_due' => 500, 'currency' => 'USD']);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.mark-paid', $order->uuid))
        ->assertRedirect();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
});

test('marking paid creates a payment row when there is none', function () {
    $order = StoreOrder::factory()->create(['total' => 500, 'amount_due' => 500, 'currency' => 'USD']);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.mark-paid', $order->uuid));

    $payment = $order->payments()->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toEqual(StorePaymentStatus::COMPLETED);
    expect((int) $payment->amount)->toEqual(500);
});

test('an already paid order cannot be marked paid again', function () {
    [$order] = orderAdminPaidOrder();

    $this->actingAs($this->superadmin)->post(route('admin.store.order.mark-paid', $order->uuid));

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::COMPLETED);
    Queue::assertNotPushed(ProcessStoreOrderPurchaseJob::class);
});

test('marking paid needs the update permission', function () {
    $order = StoreOrder::factory()->create();

    $this->actingAs(staffWith(['read store_orders']))
        ->post(route('admin.store.order.mark-paid', $order->uuid))
        ->assertForbidden();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('cancelling releases the coupon and the gift card', function () {
    $coupon = StoreCoupon::factory()->create(['used_count' => 1]);
    $card = StoreGiftCard::create([
        'code' => 'GC1', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
    ]);

    $order = StoreOrder::factory()->create([
        'total' => 1000, 'amount_due' => 500, 'currency' => 'USD',
        'store_gift_card_id' => $card->id,
        'gift_card_amount' => 500,
    ]);
    $this->recordOrderCoupon($order, $coupon, 500);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.cancel', $order->uuid), ['reason' => 'Buyer changed their mind'])
        ->assertRedirect();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CANCELLED);
    expect((int) $coupon->fresh()->used_count)->toEqual(0);
    $this->assertStringContainsString('Buyer changed their mind', $order->fresh()->notes);
});

test('cancelling needs the update permission', function () {
    $order = StoreOrder::factory()->create();

    $this->actingAs(staffWith(['read store_orders']))
        ->post(route('admin.store.order.cancel', $order->uuid))
        ->assertForbidden();
});

test('a book only refund is recorded without calling the gateway', function () {
    [$order, $payment] = orderAdminPaidOrder(2000);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.refund', $order->uuid), [
            'amount' => 2000, 'reason' => 'Duplicate order', 'at_gateway' => false,
        ])
        ->assertRedirect();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);

    $refund = $payment->fresh()->refunds()->first();
    expect($refund->type)->toEqual(StorePaymentRefundType::REFUND);
    expect((int) $refund->amount)->toEqual(2000);
    expect($refund->gateway_refund_id)->toBeNull('Nothing moved at the gateway, so there is no gateway id.');
    expect($refund->created_by)->toEqual($this->superadmin->id);
});

test('a partial refund leaves the order partially refunded', function () {
    [$order, $payment] = orderAdminPaidOrder(2000);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.refund', $order->uuid), [
        'amount' => 500, 'at_gateway' => false,
    ]);

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
    expect((int) $payment->fresh()->refunded_amount)->toEqual(500);
});

test('refunding more than remains is rejected', function () {
    [$order] = orderAdminPaidOrder(2000);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.refund', $order->uuid), ['amount' => 2001, 'at_gateway' => false])
        ->assertSessionHasErrors('amount');

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::COMPLETED);
});

test('a second refund cannot exceed what is left', function () {
    [$order] = orderAdminPaidOrder(2000);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.refund', $order->uuid), [
        'amount' => 1500, 'at_gateway' => false,
    ]);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.refund', $order->uuid), ['amount' => 1000, 'at_gateway' => false])
        ->assertSessionHasErrors('amount');
});

test('refunding an order with no completed payment is rejected', function () {
    $order = StoreOrder::factory()->completed()->create();

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.refund', $order->uuid), ['amount' => 100, 'at_gateway' => false])
        ->assertSessionHasErrors('amount');
});

test('a gateway refund that throws records nothing', function () {
    // An order marked refunded when the money never moved is worse than an error.
    [$order, $payment] = orderAdminPaidOrder(2000);
    $payment->update(['gateway' => StorePaymentGateway::MANUAL]);

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.refund', $order->uuid), ['amount' => 2000, 'at_gateway' => true])
        ->assertSessionHasErrors('amount');

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::COMPLETED);
    expect($payment->fresh()->refunds()->count())->toEqual(0);
});

test('a full refund revokes the grants', function () {
    [$order] = orderAdminPaidOrder(2000);
    $package = StorePackage::factory()->create();
    $item = $order->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 2000, 'unit_price' => 2000, 'total' => 2000,
    ]);
    $grant = $item->grant()->create([
        'store_package_id' => $package->id, 'player_uuid' => $order->player_uuid,
        'status' => StorePackageGrantStatus::ACTIVE, 'granted_at' => now(),
    ]);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.refund', $order->uuid), [
        'amount' => 2000, 'at_gateway' => false,
    ]);

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
});

test('refunding needs the refund permission not merely update', function () {
    [$order] = orderAdminPaidOrder();

    $this->actingAs(staffWith(['read store_orders', 'update store_orders']))
        ->post(route('admin.store.order.refund', $order->uuid), ['amount' => 100, 'at_gateway' => false])
        ->assertForbidden();
});

// --- Resend -------------------------------------------------------------------------------------------
function deliveryFor(StoreOrder $order, CommandQueueStatus $status): StoreOrderDelivery
{
    $package = StorePackage::factory()->create();
    $item = $order->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 100, 'unit_price' => 100, 'total' => 100,
    ]);

    $queue = CommandQueue::create([
        'server_id' => Server::factory()->create()->id,
        'parsed_command' => 'give Notch diamond 1',
        'config' => ['is_player_online_required' => false],
        'status' => $status,
        'max_attempts' => 3,
    ]);

    return StoreOrderDelivery::create([
        'store_order_id' => $order->id,
        'store_order_item_id' => $item->id,
        'store_command_id' => null,
        'server_id' => $queue->server_id,
        'command_queue_id' => $queue->id,
        'trigger' => StoreCommandTrigger::PURCHASE,
        'parsed_command' => 'give Notch diamond 1',
        'repeat_index' => 0,
    ]);
}

test('resending re queues a failed delivery without creating a second one', function () {
    // The unique index means a naive re-dispatch would be a silent no-op, so the resend path
    // must reuse the delivery row. Duplicating it would deliver the package twice.
    [$order] = orderAdminPaidOrder();
    $delivery = deliveryFor($order, CommandQueueStatus::FAILED);
    $originalQueueId = $delivery->command_queue_id;

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.order.resend', $order->uuid))
        ->assertRedirect();

    $delivery->refresh();
    expect(StoreOrderDelivery::count())->toEqual(1, 'Re-sending must not create a second delivery record.');
    $this->assertNotEquals($originalQueueId, $delivery->command_queue_id);
    expect($delivery->redispatch_count)->toEqual(1);
    expect($delivery->commandQueue->status)->toEqual(CommandQueueStatus::PENDING);
    expect($delivery->commandQueue->tag)->toEqual('store');

    Queue::assertPushed(RunCommandQueueJob::class);
});

test('resending leaves a completed delivery alone', function () {
    [$order] = orderAdminPaidOrder();
    $delivery = deliveryFor($order, CommandQueueStatus::COMPLETED);
    $originalQueueId = $delivery->command_queue_id;

    $this->actingAs($this->superadmin)->post(route('admin.store.order.resend', $order->uuid));

    expect($delivery->fresh()->command_queue_id)->toEqual($originalQueueId);
    expect($delivery->fresh()->redispatch_count)->toEqual(0);
});

test('resending leaves a deferred delivery alone by default', function () {
    // Deferred means "waiting for the player to log in", which is the system working.
    [$order] = orderAdminPaidOrder();
    $delivery = deliveryFor($order, CommandQueueStatus::DEFERRED);
    $originalQueueId = $delivery->command_queue_id;

    $this->actingAs($this->superadmin)->post(route('admin.store.order.resend', $order->uuid));
    expect($delivery->fresh()->command_queue_id)->toEqual($originalQueueId);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.resend', $order->uuid), [
        'include_unfinished' => true,
    ]);
    $this->assertNotEquals($originalQueueId, $delivery->fresh()->command_queue_id);
});

test('an unpaid order cannot be delivered', function () {
    $order = StoreOrder::factory()->create();
    deliveryFor($order, CommandQueueStatus::FAILED);

    $this->actingAs($this->superadmin)->post(route('admin.store.order.resend', $order->uuid));

    Queue::assertNotPushed(RunCommandQueueJob::class);
});

test('resending needs the resend permission', function () {
    [$order] = orderAdminPaidOrder();

    $this->actingAs(staffWith(['read store_orders', 'update store_orders']))
        ->post(route('admin.store.order.resend', $order->uuid))
        ->assertForbidden();
});

test('the sweep cancels a pending order past its ttl', function () {
    config(['store.pending_order_ttl_hours' => 24]);

    $stale = StoreOrder::factory()->create();
    $stale->forceFill(['created_at' => now()->subHours(25)])->save();

    (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

    expect($stale->fresh()->status)->toEqual(StoreOrderStatus::CANCELLED);
});

test('the sweep leaves a recent pending order alone', function () {
    config(['store.pending_order_ttl_hours' => 24]);

    $fresh = StoreOrder::factory()->create();
    $fresh->forceFill(['created_at' => now()->subHours(2)])->save();

    (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

    expect($fresh->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('the sweep never touches a paid order', function () {
    [$order] = orderAdminPaidOrder();
    $order->forceFill(['created_at' => now()->subDays(30)])->save();

    (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::COMPLETED);
});

test('the sweep releases the coupon a stale order was holding', function () {
    $coupon = StoreCoupon::factory()->create(['used_count' => 1, 'max_uses_total' => 1]);
    $stale = StoreOrder::factory()->create();
    $this->recordOrderCoupon($stale, $coupon, 100);
    $stale->forceFill(['created_at' => now()->subHours(48)])->save();

    (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

    expect((int) $coupon->fresh()->used_count)->toEqual(0, 'The last use of a coupon must not be locked away by an abandoned checkout.');
});

test('the sweep does nothing when the store is disabled', function () {
    config(['store.enabled' => false, 'store.pending_order_ttl_hours' => 24]);

    $stale = StoreOrder::factory()->create();
    $stale->forceFill(['created_at' => now()->subDays(5)])->save();

    (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

    expect($stale->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('delivery status stays readable through the command queue', function () {
    // store_order_deliveries deliberately has no status column of its own.
    [$order] = orderAdminPaidOrder();
    deliveryFor($order, CommandQueueStatus::FAILED);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.order.show', $order->uuid))
        ->assertInertia(function ($page) {
            $delivery = $page->toArray()['props']['order']['deliveries'][0];

            $this->assertArrayNotHasKey('status', $delivery);
            expect($delivery['command_queue']['status']['value'])->toEqual(CommandQueueStatus::FAILED->value);
        });
});

test('an order the delivery status of which is partial is reported as such', function () {
    $order = StoreOrder::factory()->paid()->create();

    app(StoreOrderService::class)->markCompleted($order, StoreDeliveryStatus::PARTIAL);

    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::PARTIAL);
});

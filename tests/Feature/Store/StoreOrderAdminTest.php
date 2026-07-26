<?php

namespace Tests\Feature\Store;

use App\Enums\CommandQueueStatus;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
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
use Tests\TestCase;

class StoreOrderAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        StoreCurrency::factory()->base()->create();

        Queue::fake([ProcessStoreOrderPurchaseJob::class, RunCommandQueueJob::class]);

        $this->superadmin = User::whereId(1)->first();
    }

    /**
     * A staff user carrying exactly the listed store permissions, so each action's own permission
     * can be tested rather than the blanket superadmin bypass.
     */
    private function staffWith(array $permissions): User
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

    private function paidOrder(int $amount = 2000): array
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

    // --- Access matrix -------------------------------------------------------------------------

    public function test_a_guest_is_redirected_to_login()
    {
        $this->get(route('admin.store-order.index'))->assertRedirect(route('login'));
    }

    public function test_a_non_staff_user_cannot_reach_the_listing()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.store-order.index'))
            ->assertStatus(302);
    }

    public function test_staff_without_read_permission_are_forbidden()
    {
        $this->actingAs($this->staffWith(['read store_packages']))
            ->get(route('admin.store-order.index'))
            ->assertForbidden();
    }

    public function test_the_listing_is_hidden_when_the_store_is_disabled()
    {
        config(['store.enabled' => false]);

        $this->actingAs($this->staffWith(['read store_orders']))
            ->get(route('admin.store-order.index'))
            ->assertForbidden();
    }

    // --- Listing ---------------------------------------------------------------------------------

    public function test_the_listing_renders_with_orders()
    {
        StoreOrder::factory()->count(3)->create();

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreOrder/IndexStoreOrder')
                ->has('orders.data', 3)
            );
    }

    public function test_each_row_carries_a_total_formatted_in_its_own_currency()
    {
        // Two orders in different currencies must not be formatted with one shared symbol.
        StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'symbol' => '¥', 'is_base' => false, 'rate_to_base' => 150]);

        StoreOrder::factory()->create(['currency' => 'USD', 'total' => 1999]);
        StoreOrder::factory()->create(['currency' => 'JPY', 'total' => 1000]);

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.index'))
            ->assertInertia(function ($page) {
                $totals = collect($page->toArray()['props']['orders']['data'])->pluck('total_formatted');

                $this->assertTrue($totals->contains('$19.99'));
                // Zero-decimal: ¥1000 is a thousand yen, not ten.
                $this->assertTrue($totals->contains('¥1,000'));
            });
    }

    public function test_the_listing_can_be_filtered_by_player_username()
    {
        StoreOrder::factory()->create(['player_username' => 'Notch']);
        StoreOrder::factory()->create(['player_username' => 'Herobrine']);

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.index', ['filter' => ['q' => 'Notch']]))
            ->assertInertia(fn ($page) => $page->has('orders.data', 1));
    }

    // --- Detail -----------------------------------------------------------------------------------

    public function test_the_detail_page_renders()
    {
        [$order] = $this->paidOrder();

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.show', $order->uuid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreOrder/ShowStoreOrder')
                ->has('order')
                ->has('money.total')
                ->has('permissions')
            );
    }

    public function test_a_delivery_deferred_past_the_attention_window_is_flagged()
    {
        config(['store.deferred_attention_days' => 3]);

        [$order] = $this->paidOrder();
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
            'store_package_command_id' => null,
            'server_id' => $queue->server_id,
            'command_queue_id' => $queue->id,
            'trigger' => StorePackageCommandTrigger::PURCHASE,
            'parsed_command' => 'give Notch diamond 1',
            'repeat_index' => 0,
        ]);
        $delivery->forceFill(['created_at' => now()->subDays(5)])->save();

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.show', $order->uuid))
            ->assertInertia(fn ($page) => $page->has('stuckDeliveries', 1));
    }

    // --- Mark paid ---------------------------------------------------------------------------------

    public function test_an_admin_can_mark_a_pending_order_paid()
    {
        $order = StoreOrder::factory()->create(['total' => 500, 'amount_due' => 500, 'currency' => 'USD']);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.mark-paid', $order->uuid))
            ->assertRedirect();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
    }

    public function test_marking_paid_creates_a_payment_row_when_there_is_none()
    {
        $order = StoreOrder::factory()->create(['total' => 500, 'amount_due' => 500, 'currency' => 'USD']);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.mark-paid', $order->uuid));

        $payment = $order->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(StorePaymentStatus::COMPLETED, $payment->status);
        $this->assertEquals(500, (int) $payment->amount);
    }

    public function test_an_already_paid_order_cannot_be_marked_paid_again()
    {
        [$order] = $this->paidOrder();

        $this->actingAs($this->superadmin)->post(route('admin.store-order.mark-paid', $order->uuid));

        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->fresh()->status);
        Queue::assertNotPushed(ProcessStoreOrderPurchaseJob::class);
    }

    public function test_marking_paid_needs_the_update_permission()
    {
        $order = StoreOrder::factory()->create();

        $this->actingAs($this->staffWith(['read store_orders']))
            ->post(route('admin.store-order.mark-paid', $order->uuid))
            ->assertForbidden();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    // --- Cancel --------------------------------------------------------------------------------------

    public function test_cancelling_releases_the_coupon_and_the_gift_card()
    {
        $coupon = StoreCoupon::factory()->create(['used_count' => 1]);
        $card = StoreGiftCard::create([
            'code' => 'GC1', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
        ]);

        $order = StoreOrder::factory()->create([
            'total' => 1000, 'amount_due' => 500, 'currency' => 'USD',
            'store_coupon_id' => $coupon->id,
            'store_gift_card_id' => $card->id,
            'gift_card_amount' => 500,
        ]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.cancel', $order->uuid), ['reason' => 'Buyer changed their mind'])
            ->assertRedirect();

        $this->assertEquals(StoreOrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(0, (int) $coupon->fresh()->used_count);
        $this->assertStringContainsString('Buyer changed their mind', $order->fresh()->notes);
    }

    public function test_cancelling_needs_the_update_permission()
    {
        $order = StoreOrder::factory()->create();

        $this->actingAs($this->staffWith(['read store_orders']))
            ->post(route('admin.store-order.cancel', $order->uuid))
            ->assertForbidden();
    }

    // --- Refund -----------------------------------------------------------------------------------------

    public function test_a_book_only_refund_is_recorded_without_calling_the_gateway()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.refund', $order->uuid), [
                'amount' => 2000, 'reason' => 'Duplicate order', 'at_gateway' => false,
            ])
            ->assertRedirect();

        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);

        $refund = $payment->fresh()->refunds()->first();
        $this->assertEquals(StorePaymentRefundType::REFUND, $refund->type);
        $this->assertEquals(2000, (int) $refund->amount);
        $this->assertNull($refund->gateway_refund_id, 'Nothing moved at the gateway, so there is no gateway id.');
        $this->assertEquals($this->superadmin->id, $refund->created_by);
    }

    public function test_a_partial_refund_leaves_the_order_partially_refunded()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.refund', $order->uuid), [
            'amount' => 500, 'at_gateway' => false,
        ]);

        $this->assertEquals(StoreOrderStatus::PARTIALLY_REFUNDED, $order->fresh()->status);
        $this->assertEquals(500, (int) $payment->fresh()->refunded_amount);
    }

    public function test_refunding_more_than_remains_is_rejected()
    {
        [$order] = $this->paidOrder(2000);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.refund', $order->uuid), ['amount' => 2001, 'at_gateway' => false])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_a_second_refund_cannot_exceed_what_is_left()
    {
        [$order] = $this->paidOrder(2000);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.refund', $order->uuid), [
            'amount' => 1500, 'at_gateway' => false,
        ]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.refund', $order->uuid), ['amount' => 1000, 'at_gateway' => false])
            ->assertSessionHasErrors('amount');
    }

    public function test_refunding_an_order_with_no_completed_payment_is_rejected()
    {
        $order = StoreOrder::factory()->completed()->create();

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.refund', $order->uuid), ['amount' => 100, 'at_gateway' => false])
            ->assertSessionHasErrors('amount');
    }

    public function test_a_gateway_refund_that_throws_records_nothing()
    {
        // An order marked refunded when the money never moved is worse than an error.
        [$order, $payment] = $this->paidOrder(2000);
        $payment->update(['gateway' => StorePaymentGateway::MANUAL]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.refund', $order->uuid), ['amount' => 2000, 'at_gateway' => true])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->fresh()->status);
        $this->assertEquals(0, $payment->fresh()->refunds()->count());
    }

    public function test_a_full_refund_revokes_the_grants()
    {
        [$order] = $this->paidOrder(2000);
        $package = StorePackage::factory()->create();
        $item = $order->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 2000, 'unit_price' => 2000, 'total' => 2000,
        ]);
        $grant = $item->grant()->create([
            'store_package_id' => $package->id, 'player_uuid' => $order->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE, 'granted_at' => now(),
        ]);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.refund', $order->uuid), [
            'amount' => 2000, 'at_gateway' => false,
        ]);

        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->fresh()->status);
    }

    public function test_refunding_needs_the_refund_permission_not_merely_update()
    {
        [$order] = $this->paidOrder();

        $this->actingAs($this->staffWith(['read store_orders', 'update store_orders']))
            ->post(route('admin.store-order.refund', $order->uuid), ['amount' => 100, 'at_gateway' => false])
            ->assertForbidden();
    }

    // --- Resend -------------------------------------------------------------------------------------------

    private function deliveryFor(StoreOrder $order, CommandQueueStatus $status): StoreOrderDelivery
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
            'store_package_command_id' => null,
            'server_id' => $queue->server_id,
            'command_queue_id' => $queue->id,
            'trigger' => StorePackageCommandTrigger::PURCHASE,
            'parsed_command' => 'give Notch diamond 1',
            'repeat_index' => 0,
        ]);
    }

    public function test_resending_re_queues_a_failed_delivery_without_creating_a_second_one()
    {
        // The unique index means a naive re-dispatch would be a silent no-op, so the resend path
        // must reuse the delivery row. Duplicating it would deliver the package twice.
        [$order] = $this->paidOrder();
        $delivery = $this->deliveryFor($order, CommandQueueStatus::FAILED);
        $originalQueueId = $delivery->command_queue_id;

        $this->actingAs($this->superadmin)
            ->post(route('admin.store-order.resend', $order->uuid))
            ->assertRedirect();

        $delivery->refresh();
        $this->assertEquals(1, StoreOrderDelivery::count(), 'Re-sending must not create a second delivery record.');
        $this->assertNotEquals($originalQueueId, $delivery->command_queue_id);
        $this->assertEquals(1, $delivery->redispatch_count);
        $this->assertEquals(CommandQueueStatus::PENDING, $delivery->commandQueue->status);
        $this->assertEquals('store', $delivery->commandQueue->tag);

        Queue::assertPushed(RunCommandQueueJob::class);
    }

    public function test_resending_leaves_a_completed_delivery_alone()
    {
        [$order] = $this->paidOrder();
        $delivery = $this->deliveryFor($order, CommandQueueStatus::COMPLETED);
        $originalQueueId = $delivery->command_queue_id;

        $this->actingAs($this->superadmin)->post(route('admin.store-order.resend', $order->uuid));

        $this->assertEquals($originalQueueId, $delivery->fresh()->command_queue_id);
        $this->assertEquals(0, $delivery->fresh()->redispatch_count);
    }

    public function test_resending_leaves_a_deferred_delivery_alone_by_default()
    {
        // Deferred means "waiting for the player to log in", which is the system working.
        [$order] = $this->paidOrder();
        $delivery = $this->deliveryFor($order, CommandQueueStatus::DEFERRED);
        $originalQueueId = $delivery->command_queue_id;

        $this->actingAs($this->superadmin)->post(route('admin.store-order.resend', $order->uuid));
        $this->assertEquals($originalQueueId, $delivery->fresh()->command_queue_id);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.resend', $order->uuid), [
            'include_unfinished' => true,
        ]);
        $this->assertNotEquals($originalQueueId, $delivery->fresh()->command_queue_id);
    }

    public function test_an_unpaid_order_cannot_be_delivered()
    {
        $order = StoreOrder::factory()->create();
        $this->deliveryFor($order, CommandQueueStatus::FAILED);

        $this->actingAs($this->superadmin)->post(route('admin.store-order.resend', $order->uuid));

        Queue::assertNotPushed(RunCommandQueueJob::class);
    }

    public function test_resending_needs_the_resend_permission()
    {
        [$order] = $this->paidOrder();

        $this->actingAs($this->staffWith(['read store_orders', 'update store_orders']))
            ->post(route('admin.store-order.resend', $order->uuid))
            ->assertForbidden();
    }

    // --- Stale pending sweep ---------------------------------------------------------------------------------

    public function test_the_sweep_cancels_a_pending_order_past_its_ttl()
    {
        config(['store.pending_order_ttl_hours' => 24]);

        $stale = StoreOrder::factory()->create();
        $stale->forceFill(['created_at' => now()->subHours(25)])->save();

        (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

        $this->assertEquals(StoreOrderStatus::CANCELLED, $stale->fresh()->status);
    }

    public function test_the_sweep_leaves_a_recent_pending_order_alone()
    {
        config(['store.pending_order_ttl_hours' => 24]);

        $fresh = StoreOrder::factory()->create();
        $fresh->forceFill(['created_at' => now()->subHours(2)])->save();

        (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

        $this->assertEquals(StoreOrderStatus::PENDING, $fresh->fresh()->status);
    }

    public function test_the_sweep_never_touches_a_paid_order()
    {
        [$order] = $this->paidOrder();
        $order->forceFill(['created_at' => now()->subDays(30)])->save();

        (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->fresh()->status);
    }

    public function test_the_sweep_releases_the_coupon_a_stale_order_was_holding()
    {
        $coupon = StoreCoupon::factory()->create(['used_count' => 1, 'max_uses_total' => 1]);
        $stale = StoreOrder::factory()->create(['store_coupon_id' => $coupon->id]);
        $stale->forceFill(['created_at' => now()->subHours(48)])->save();

        (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

        $this->assertEquals(0, (int) $coupon->fresh()->used_count, 'The last use of a coupon must not be locked away by an abandoned checkout.');
    }

    public function test_the_sweep_does_nothing_when_the_store_is_disabled()
    {
        config(['store.enabled' => false, 'store.pending_order_ttl_hours' => 24]);

        $stale = StoreOrder::factory()->create();
        $stale->forceFill(['created_at' => now()->subDays(5)])->save();

        (new ExpireStalePendingStoreOrdersJob)->handle(app(StoreOrderService::class));

        $this->assertEquals(StoreOrderStatus::PENDING, $stale->fresh()->status);
    }

    public function test_delivery_status_stays_readable_through_the_command_queue()
    {
        // store_order_deliveries deliberately has no status column of its own.
        [$order] = $this->paidOrder();
        $this->deliveryFor($order, CommandQueueStatus::FAILED);

        $this->actingAs($this->superadmin)
            ->get(route('admin.store-order.show', $order->uuid))
            ->assertInertia(function ($page) {
                $delivery = $page->toArray()['props']['order']['deliveries'][0];

                $this->assertArrayNotHasKey('status', $delivery);
                $this->assertEquals(CommandQueueStatus::FAILED->value, $delivery['command_queue']['status']['value']);
            });
    }

    public function test_an_order_the_delivery_status_of_which_is_partial_is_reported_as_such()
    {
        $order = StoreOrder::factory()->paid()->create();

        app(StoreOrderService::class)->markCompleted($order, StoreDeliveryStatus::PARTIAL);

        $this->assertEquals(StoreDeliveryStatus::PARTIAL, $order->fresh()->delivery_status);
    }
}

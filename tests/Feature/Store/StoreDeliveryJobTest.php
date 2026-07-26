<?php

namespace Tests\Feature\Store;

use App\Enums\CommandQueueStatus;
use App\Enums\StoreCommandTarget;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePayment;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreDeliveryJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();
        Queue::fake([RunCommandQueueJob::class]);
    }

    /**
     * @return array{0: StoreOrder, 1: StorePackage, 2: Server}
     */
    private function paidOrder(array $packageAttributes = [], int $quantity = 1): array
    {
        $server = Server::factory()->create();
        $package = StorePackage::factory()->create(array_merge(['price' => 1000], $packageAttributes));
        $package->servers()->attach($server);

        $order = StoreOrder::factory()->paid()->create([
            'player_username' => 'Steve',
            'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        ]);
        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => $quantity,
            'unit_price_original' => 1000,
            'unit_price' => 1000,
            'total' => 1000 * $quantity,
            'expiry_duration_days' => $package->expiry_duration_days,
        ]);

        return [$order->fresh(), $package, $server];
    }

    private function purchaseCommand(StorePackage $package, array $attributes = []): StorePackageCommand
    {
        return StorePackageCommand::factory()->create(array_merge([
            'store_package_id' => $package->id,
            'trigger' => StorePackageCommandTrigger::PURCHASE,
            'command' => 'lp user {PLAYER_USERNAME} parent add vip',
            'target' => StoreCommandTarget::PACKAGE_SERVERS,
        ], $attributes));
    }

    private function runJob(StoreOrder $order): void
    {
        (new ProcessStoreOrderPurchaseJob($order))->handle(
            app(StoreCommandDispatchService::class),
            app(StoreOrderService::class),
        );
    }

    public function test_a_paid_order_queues_its_purchase_commands()
    {
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);

        $this->runJob($order);

        $queue = CommandQueue::where('tag', 'store')->first();
        $this->assertNotNull($queue);
        $this->assertEquals('lp user Steve parent add vip', $queue->parsed_command);
        $this->assertEquals(CommandQueueStatus::PENDING, $queue->status);
        Queue::assertPushed(RunCommandQueueJob::class);
    }

    public function test_the_queue_row_always_carries_the_online_flag_in_config()
    {
        // RunCommandQueueJob reads config[is_player_online_required] directly; a row without it
        // used to throw.
        [$order, $package] = $this->paidOrder(['is_player_online_required' => true]);
        $this->purchaseCommand($package);

        $this->runJob($order);

        $config = CommandQueue::where('tag', 'store')->first()->config;
        $this->assertArrayHasKey('is_player_online_required', $config);
        $this->assertTrue($config['is_player_online_required']);
    }

    public function test_max_attempts_allows_the_sweeper_to_retry()
    {
        // Every pre-existing caller uses 1, which means the sweeper never retries anything.
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);

        $this->runJob($order);

        $this->assertGreaterThan(1, CommandQueue::where('tag', 'store')->first()->max_attempts);
    }

    public function test_a_command_can_override_the_packages_online_requirement()
    {
        [$order, $package] = $this->paidOrder(['is_player_online_required' => false]);
        $this->purchaseCommand($package, ['is_player_online_required' => true]);

        $this->runJob($order);

        $this->assertTrue(CommandQueue::where('tag', 'store')->first()->config['is_player_online_required']);
    }

    public function test_a_null_command_flag_inherits_from_the_package()
    {
        [$order, $package] = $this->paidOrder(['is_player_online_required' => true]);
        $this->purchaseCommand($package, ['is_player_online_required' => null]);

        $this->runJob($order);

        $this->assertTrue(CommandQueue::where('tag', 'store')->first()->config['is_player_online_required']);
    }

    public function test_all_the_documented_placeholders_are_substituted()
    {
        [$order, $package] = $this->paidOrder([], 3);
        $this->purchaseCommand($package, [
            'command' => 'give {PLAYER_USERNAME} {PLAYER_UUID} {QUANTITY} {PACKAGE_NAME} {ORDER_UUID}',
        ]);

        $this->runJob($order);

        $parsed = CommandQueue::where('tag', 'store')->first()->parsed_command;
        $this->assertStringContainsString('Steve', $parsed);
        $this->assertStringContainsString('069a79f4-44e9-4726-a5be-fca90e38aaf5', $parsed);
        $this->assertStringContainsString('3', $parsed);
        $this->assertStringContainsString($package->name, $parsed);
        $this->assertStringContainsString($order->uuid, $parsed);
        $this->assertStringNotContainsString('{', $parsed, 'No placeholder should survive substitution.');
    }

    public function test_option_placeholders_are_substituted_from_the_order_item_snapshot()
    {
        [$order, $package] = $this->paidOrder();
        $order->items->first()->update([
            'options' => [['placeholder' => 'TIER', 'value' => 'diamond', 'name' => 'Diamond']],
        ]);
        $this->purchaseCommand($package, ['command' => 'kit give {PLAYER_USERNAME} {TIER}']);

        $this->runJob($order->fresh());

        $this->assertEquals('kit give Steve diamond', CommandQueue::where('tag', 'store')->first()->parsed_command);
    }

    public function test_quantity_substitution_by_default()
    {
        [$order, $package] = $this->paidOrder(['is_command_repeated_per_quantity' => false], 5);
        $this->purchaseCommand($package, ['command' => 'give {PLAYER_USERNAME} diamond {QUANTITY}']);

        $this->runJob($order);

        $this->assertEquals(1, CommandQueue::where('tag', 'store')->count());
        $this->assertEquals('give Steve diamond 5', CommandQueue::first()->parsed_command);
    }

    public function test_repeat_per_quantity_creates_one_row_per_unit()
    {
        // Crate keys and similar: the command has to run N times rather than take a count.
        [$order, $package] = $this->paidOrder(['is_command_repeated_per_quantity' => true], 3);
        $this->purchaseCommand($package, ['command' => 'crate give {PLAYER_USERNAME} vote {QUANTITY}']);

        $this->runJob($order);

        $queues = CommandQueue::where('tag', 'store')->get();
        $this->assertCount(3, $queues);
        // Each run is for a single unit, so QUANTITY is 1 rather than 3.
        $this->assertEquals('crate give Steve vote 1', $queues->first()->parsed_command);
    }

    public function test_a_delayed_command_sets_execute_at_and_is_left_for_the_sweeper()
    {
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package, ['delay_seconds' => 600]);

        $this->runJob($order);

        $queue = CommandQueue::where('tag', 'store')->first();
        $this->assertNotNull($queue->execute_at);
        $this->assertEquals(CommandQueueStatus::PENDING, $queue->status);
        Queue::assertNotPushed(RunCommandQueueJob::class);
    }

    public function test_a_command_runs_on_every_server_when_targeted_at_all()
    {
        [$order, $package] = $this->paidOrder();
        Server::factory()->count(2)->create();
        $this->purchaseCommand($package, ['target' => StoreCommandTarget::ALL_SERVERS]);

        $this->runJob($order);

        $this->assertEquals(3, CommandQueue::where('tag', 'store')->count());
    }

    public function test_servers_without_a_webquery_port_are_excluded()
    {
        [$order, $package] = $this->paidOrder();
        $package->servers()->attach(Server::factory()->create(['webquery_port' => null]));
        $this->purchaseCommand($package);

        $this->runJob($order);

        // A server with no webquery port could never receive the command.
        $this->assertEquals(1, CommandQueue::where('tag', 'store')->count());
    }

    public function test_only_the_matching_trigger_is_dispatched()
    {
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);
        StorePackageCommand::factory()->expiry()->create(['store_package_id' => $package->id]);
        StorePackageCommand::factory()->refund()->create(['store_package_id' => $package->id]);

        $this->runJob($order);

        $this->assertEquals(1, CommandQueue::where('tag', 'store')->count());
    }

    public function test_rerunning_the_job_does_not_deliver_twice()
    {
        // The whole reason store_order_deliveries carries a unique index: a webhook replay or an
        // admin "retry all failed" must not hand out the purchase again.
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);

        $this->runJob($order);
        $this->runJob($order->fresh());

        $this->assertEquals(1, CommandQueue::where('tag', 'store')->count());
        $this->assertEquals(1, StoreOrderDelivery::count());
    }

    public function test_a_delivery_row_records_what_was_queued()
    {
        [$order, $package, $server] = $this->paidOrder();
        $command = $this->purchaseCommand($package);

        $this->runJob($order);

        $delivery = StoreOrderDelivery::first();
        $this->assertEquals($order->id, $delivery->store_order_id);
        $this->assertEquals($command->id, $delivery->store_package_command_id);
        $this->assertEquals($server->id, $delivery->server_id);
        $this->assertEquals(StorePackageCommandTrigger::PURCHASE, $delivery->trigger);
        $this->assertEquals('lp user Steve parent add vip', $delivery->parsed_command);
        $this->assertNotNull($delivery->commandQueue);
    }

    public function test_a_grant_is_issued_per_item()
    {
        [$order, $package] = $this->paidOrder(['expiry_duration_days' => 30]);
        $this->purchaseCommand($package);

        $this->runJob($order);

        $grant = $order->items->first()->grant;
        $this->assertEquals(StorePackageGrantStatus::ACTIVE, $grant->status);
        $this->assertNotNull($grant->expires_at);
        $this->assertEqualsWithDelta(30, now()->diffInDays($grant->expires_at), 1);
    }

    public function test_a_permanent_package_grant_has_no_expiry()
    {
        [$order, $package] = $this->paidOrder(['expiry_duration_days' => null]);
        $this->purchaseCommand($package);

        $this->runJob($order);

        $this->assertNull($order->items->first()->grant->expires_at);
    }

    public function test_sold_count_is_incremented_only_once_when_paid()
    {
        [$order, $package] = $this->paidOrder([], 2);
        $this->purchaseCommand($package);

        $this->runJob($order);
        $this->assertEquals(2, $package->fresh()->sold_count);

        // A retry must not inflate stock consumption.
        $this->runJob($order->fresh());
        $this->assertEquals(2, $package->fresh()->sold_count);
    }

    public function test_the_order_is_completed_after_delivery_is_queued()
    {
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);

        $this->runJob($order);

        $order->refresh();
        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->status);
        $this->assertEquals(StoreDeliveryStatus::PENDING, $order->delivery_status);
    }

    public function test_a_package_with_no_commands_still_completes_the_order()
    {
        [$order] = $this->paidOrder();

        $this->runJob($order);

        $order->refresh();
        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->status);
        $this->assertEquals(StoreDeliveryStatus::DELIVERED, $order->delivery_status, 'Nothing to deliver counts as delivered.');
    }

    public function test_delivery_is_marked_failed_when_no_server_can_receive_it()
    {
        $package = StorePackage::factory()->create();
        $package->servers()->attach(Server::factory()->create(['webquery_port' => null]));
        $order = StoreOrder::factory()->paid()->create();
        $order->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);
        $this->purchaseCommand($package);

        $this->runJob($order->fresh());

        $this->assertEquals(StoreDeliveryStatus::FAILED, $order->fresh()->delivery_status);
    }

    public function test_a_refunded_order_is_not_delivered()
    {
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);
        $order->update(['status' => StoreOrderStatus::REFUNDED]);

        $this->runJob($order->fresh());

        $this->assertEquals(0, CommandQueue::where('tag', 'store')->count());
    }

    public function test_paying_an_order_dispatches_the_fulfilment_job()
    {
        Queue::fake();
        [$order, $package] = $this->paidOrder();
        $this->purchaseCommand($package);

        $pending = StoreOrder::factory()->create(['total' => 1000, 'amount_due' => 1000]);
        $payment = StorePayment::factory()->create([
            'store_order_id' => $pending->id, 'amount' => 1000, 'currency' => 'USD',
        ]);

        app(StoreOrderService::class)->markPaid($pending, $payment, 1000, 'USD');

        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
    }
}

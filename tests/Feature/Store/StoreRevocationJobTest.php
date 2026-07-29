<?php

namespace Tests\Feature\Store;

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Jobs\Store\ProcessStoreOrderRevocationJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePayment;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Refund and chargeback revocation: taking the purchase back off the player in game, not just
 * revoking the grant in the database.
 */
class StoreRevocationJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        Server::factory()->create();
    }

    /**
     * A paid order for a package carrying both revocation command sets.
     */
    private function paidOrder(): StoreOrder
    {
        $package = StorePackage::factory()->create(['price' => 1000]);

        foreach ([StorePackageCommandTrigger::REFUND, StorePackageCommandTrigger::CHARGEBACK] as $trigger) {
            StorePackageCommand::factory()->create([
                'store_package_id' => $package->id,
                'trigger' => $trigger,
                'command' => 'lp user {PLAYER_USERNAME} parent remove vip # '.$trigger->value,
            ]);
        }

        $order = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);
        $item = $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 1,
            'unit_price_original' => 1000,
            'unit_price' => 1000,
            'total' => 1000,
        ]);
        $item->grant()->create([
            'store_package_id' => $package->id,
            'player_uuid' => $order->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE,
            'granted_at' => now(),
        ]);

        StorePayment::factory()->create([
            'store_order_id' => $order->id,
            'amount' => 1000,
            'currency' => $order->currency,
            'status' => StorePaymentStatus::COMPLETED,
        ]);

        return $order->fresh();
    }

    /**
     * @return array<int, string>
     */
    private function queuedCommands(): array
    {
        return CommandQueue::where('tag', 'store')->pluck('parsed_command')->all();
    }

    // --- The job itself ---------------------------------------------------------------------------

    public function test_the_job_queues_the_refund_commands()
    {
        $order = $this->paidOrder();

        (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
            ->handle(app(StoreCommandDispatchService::class));

        $this->assertSame(
            ['lp user '.$order->player_username.' parent remove vip # refund'],
            $this->queuedCommands()
        );
        $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'refund']);
    }

    public function test_the_job_queues_the_chargeback_commands()
    {
        $order = $this->paidOrder();

        (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::CHARGEBACK))
            ->handle(app(StoreCommandDispatchService::class));

        $this->assertSame(
            ['lp user '.$order->player_username.' parent remove vip # chargeback'],
            $this->queuedCommands()
        );
        $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'chargeback']);
    }

    public function test_re_running_the_job_queues_nothing_further()
    {
        // The unique index on store_order_deliveries is what makes a retry free.
        $order = $this->paidOrder();
        $dispatcher = app(StoreCommandDispatchService::class);

        (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))->handle($dispatcher);
        (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))->handle($dispatcher);

        $this->assertCount(1, $this->queuedCommands());
    }

    public function test_the_job_leaves_the_orders_delivery_status_alone()
    {
        // delivery_status describes how the purchase went out, not how it was taken back.
        $order = $this->paidOrder();
        $before = $order->delivery_status;

        (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
            ->handle(app(StoreCommandDispatchService::class));

        $this->assertEquals($before, $order->fresh()->delivery_status);
    }

    // --- Wiring: a refund has to actually reach the job -----------------------------------------

    public function test_a_full_refund_dispatches_the_revocation()
    {
        Queue::fake([ProcessStoreOrderRevocationJob::class]);

        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 1000);

        Queue::assertPushed(ProcessStoreOrderRevocationJob::class);
        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);
    }

    public function test_a_chargeback_dispatches_the_revocation()
    {
        Queue::fake([ProcessStoreOrderRevocationJob::class]);

        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 1000, isChargeback: true);

        Queue::assertPushed(ProcessStoreOrderRevocationJob::class);
        $this->assertEquals(StoreOrderStatus::CHARGEBACK, $order->fresh()->status);
    }

    /**
     * The distinction that matters: a partial refund leaves the buyer holding what they bought, so
     * nothing is taken off them.
     */
    public function test_a_partial_refund_dispatches_nothing()
    {
        Queue::fake([ProcessStoreOrderRevocationJob::class]);

        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 400);

        Queue::assertNotPushed(ProcessStoreOrderRevocationJob::class);
        $this->assertEquals(StoreOrderStatus::PARTIALLY_REFUNDED, $order->fresh()->status);
        $this->assertEquals(
            StorePackageGrantStatus::ACTIVE,
            $order->items->first()->grant->fresh()->status,
            'A partial refund leaves the grant active.'
        );
    }

    public function test_a_refund_the_state_machine_rejects_dispatches_nothing()
    {
        Queue::fake([ProcessStoreOrderRevocationJob::class]);

        $order = $this->paidOrder();
        $order->update(['status' => StoreOrderStatus::CANCELLED]);

        $this->assertFalse(app(StoreOrderService::class)->refund($order->fresh(), 1000));

        Queue::assertNotPushed(ProcessStoreOrderRevocationJob::class);
    }

    public function test_a_chargeback_runs_the_chargeback_commands_not_the_refund_ones()
    {
        // The trigger the listener picks is only observable in what actually gets queued.
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 1000, isChargeback: true);

        $this->assertSame(
            ['lp user '.$order->player_username.' parent remove vip # chargeback'],
            $this->queuedCommands()
        );
    }

    public function test_the_refund_commands_run_end_to_end_without_faking_the_queue()
    {
        // The listener, the job and the dispatcher together — the path a real refund takes.
        $order = $this->paidOrder();

        app(StoreOrderService::class)->refund($order, 1000);

        $this->assertSame(
            ['lp user '.$order->player_username.' parent remove vip # refund'],
            $this->queuedCommands()
        );
        $this->assertEquals(
            StorePackageGrantStatus::REVOKED,
            $order->items->first()->grant->fresh()->status
        );
    }
}

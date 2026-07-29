<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Jobs\Store\ExpireStorePackageGrantsJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePackageGrant;
use App\Services\StoreCommandDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * The expiry sweep: a timed purchase whose time is up gets marked expired and has its EXPIRY
 * command set run, which is the half that actually takes the perk off the player.
 */
class StoreExpiryJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        Server::factory()->create();
    }

    private function runSweep(): void
    {
        (new ExpireStorePackageGrantsJob)->handle(app(StoreCommandDispatchService::class));
    }

    /**
     * @param  array<string, mixed>  $grantAttributes
     */
    private function grant(array $grantAttributes = [], bool $withExpiryCommand = true): StorePackageGrant
    {
        $package = StorePackage::factory()->create(['price' => 1000]);

        if ($withExpiryCommand) {
            StorePackageCommand::factory()->create([
                'store_package_id' => $package->id,
                'trigger' => StorePackageCommandTrigger::EXPIRY,
                'command' => 'lp user {PLAYER_USERNAME} parent remove vip',
            ]);
        }

        $order = StoreOrder::factory()->completed()->create();
        $item = $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 1,
            'unit_price_original' => 1000,
            'unit_price' => 1000,
            'total' => 1000,
            'expiry_duration_days' => 30,
        ]);

        return $item->grant()->create(array_merge([
            'store_package_id' => $package->id,
            'player_uuid' => $order->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE,
            'granted_at' => now()->subDays(31),
            'expires_at' => now()->subDay(),
        ], $grantAttributes));
    }

    public function test_a_lapsed_grant_is_marked_expired()
    {
        $grant = $this->grant();

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::EXPIRED, $grant->fresh()->status);
    }

    public function test_a_lapsed_grant_runs_its_expiry_commands()
    {
        // The whole point: without this the buyer keeps the rank they stopped paying for.
        $order = $this->grant()->orderItem->order;

        $this->runSweep();

        $queue = CommandQueue::where('tag', 'store')->first();

        $this->assertNotNull($queue, 'The expiry command should have been queued.');
        $this->assertSame(
            'lp user '.$order->player_username.' parent remove vip',
            $queue->parsed_command
        );
        $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'expiry']);
    }

    public function test_a_permanent_grant_is_never_swept()
    {
        $grant = $this->grant(['expires_at' => null]);

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::ACTIVE, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_a_grant_that_has_not_lapsed_yet_is_left_alone()
    {
        $grant = $this->grant(['expires_at' => now()->addDay()]);

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::ACTIVE, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_an_already_revoked_grant_is_not_expired_on_top()
    {
        // A refund already took this one back; expiring it again would run the commands twice.
        $grant = $this->grant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => now()]);

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_an_already_expired_grant_is_not_swept_twice()
    {
        $grant = $this->grant(['status' => StorePackageGrantStatus::EXPIRED]);

        $this->runSweep();

        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_running_the_sweep_again_queues_nothing_further()
    {
        $this->grant();

        $this->runSweep();
        $this->runSweep();

        $this->assertDatabaseCount('command_queues', 1);
        $this->assertDatabaseCount('store_order_deliveries', 1);
    }

    public function test_a_package_with_no_expiry_commands_is_still_marked_expired()
    {
        // Otherwise the sweep would keep finding it every five minutes forever.
        $grant = $this->grant(withExpiryCommand: false);

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::EXPIRED, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_every_lapsed_grant_in_a_backlog_is_swept()
    {
        $grants = collect(range(1, 3))->map(fn () => $this->grant());

        $this->runSweep();

        foreach ($grants as $grant) {
            $this->assertEquals(StorePackageGrantStatus::EXPIRED, $grant->fresh()->status);
        }
    }

    /**
     * The load-bearing ordering choice: commands go out before the status changes, so a sweep that
     * dies in between leaves the grant findable rather than expired-but-never-revoked.
     */
    public function test_a_grant_stays_active_when_its_expiry_dispatch_fails()
    {
        Log::spy();

        $grant = $this->grant();

        $dispatcher = $this->mock(StoreCommandDispatchService::class);
        $dispatcher->shouldReceive('dispatchForItem')->andThrow(new \RuntimeException('socket died'));

        (new ExpireStorePackageGrantsJob)->handle($dispatcher);

        $this->assertEquals(
            StorePackageGrantStatus::ACTIVE,
            $grant->fresh()->status,
            'Left active so the next sweep tries again.'
        );
        Log::shouldHaveReceived('error')->once();
    }

    public function test_a_grant_whose_package_was_hard_deleted_is_still_marked_expired()
    {
        // Reachable: the package foreign keys are nullOnDelete, so a package can be removed from
        // under a live grant. There is nothing left to run, but the grant still has to stop being
        // active or the sweep would find it forever.
        $grant = $this->grant();
        $grant->orderItem->package->forceDelete();

        $this->runSweep();

        $this->assertEquals(StorePackageGrantStatus::EXPIRED, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_deleting_an_order_item_takes_its_grant_with_it()
    {
        // Documents why the sweep never has to cope with an orphaned grant: the foreign key
        // cascades, so there is no such row to find.
        $grant = $this->grant();

        StoreOrderItem::whereKey($grant->store_order_item_id)->delete();

        $this->assertDatabaseMissing('store_package_grants', ['id' => $grant->id]);
    }
}

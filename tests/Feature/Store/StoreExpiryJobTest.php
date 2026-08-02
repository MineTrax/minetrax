<?php

use App\Enums\StoreCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ExpireStorePackageGrantsJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreCommand;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use App\Models\StorePackageGrant;
use App\Services\StoreCommandDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Server::factory()->create();

    // The queue is sync in tests, so a dispatched command would open a real socket to the
    // factory server and block for the 10s webquery timeout. Every assertion here is about
    // the command_queues / store_order_deliveries rows, which are written before dispatch.
    Queue::fake([RunCommandQueueJob::class]);
});

function runSweep(): void
{
    (new ExpireStorePackageGrantsJob)->handle(app(StoreCommandDispatchService::class));
}

/**
 * @param  array<string, mixed>  $grantAttributes
 */
function expiryJobGrant(array $grantAttributes = [], bool $withExpiryCommand = true): StorePackageGrant
{
    $package = StorePackage::factory()->create(['price' => 1000]);

    if ($withExpiryCommand) {
        StoreCommand::factory()->forOwner($package)->create([
            'trigger' => StoreCommandTrigger::EXPIRY,
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

test('a lapsed grant is marked expired', function () {
    $grant = expiryJobGrant();

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
});

test('a lapsed grant runs its expiry commands', function () {
    // The whole point: without this the buyer keeps the rank they stopped paying for.
    $order = expiryJobGrant()->orderItem->order;

    runSweep();

    $queue = CommandQueue::where('tag', 'store')->first();

    expect($queue)->not->toBeNull('The expiry command should have been queued.');
    expect($queue->parsed_command)->toBe('lp user '.$order->player_username.' parent remove vip');
    $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'expiry']);
});

test('a permanent grant is never swept', function () {
    $grant = expiryJobGrant(['expires_at' => null]);

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE);
    $this->assertDatabaseCount('command_queues', 0);
});

test('a grant that has not lapsed yet is left alone', function () {
    $grant = expiryJobGrant(['expires_at' => now()->addDay()]);

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE);
    $this->assertDatabaseCount('command_queues', 0);
});

test('an already revoked grant is not expired on top', function () {
    // A refund already took this one back; expiring it again would run the commands twice.
    $grant = expiryJobGrant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => now()]);

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
    $this->assertDatabaseCount('command_queues', 0);
});

test('an already expired grant is not swept twice', function () {
    $grant = expiryJobGrant(['status' => StorePackageGrantStatus::EXPIRED]);

    runSweep();

    $this->assertDatabaseCount('command_queues', 0);
});

test('running the sweep again queues nothing further', function () {
    expiryJobGrant();

    runSweep();
    runSweep();

    $this->assertDatabaseCount('command_queues', 1);
    $this->assertDatabaseCount('store_order_deliveries', 1);
});

test('a package with no expiry commands is still marked expired', function () {
    // Otherwise the sweep would keep finding it every five minutes forever.
    $grant = expiryJobGrant(withExpiryCommand: false);

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
    $this->assertDatabaseCount('command_queues', 0);
});

test('every lapsed grant in a backlog is swept', function () {
    $grants = collect(range(1, 3))->map(fn () => expiryJobGrant());

    runSweep();

    foreach ($grants as $grant) {
        expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
    }
});

test('a grant stays active when its expiry dispatch fails', function () {
    Log::spy();

    $grant = expiryJobGrant();

    $dispatcher = $this->mock(StoreCommandDispatchService::class);
    $dispatcher->shouldReceive('dispatchForItem')->andThrow(new RuntimeException('socket died'));

    (new ExpireStorePackageGrantsJob)->handle($dispatcher);

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE, 'Left active so the next sweep tries again.');
    Log::shouldHaveReceived('error')->once();
});

test('a grant whose package was hard deleted is still marked expired', function () {
    // Reachable: the package foreign keys are nullOnDelete, so a package can be removed from
    // under a live grant. There is nothing left to run, but the grant still has to stop being
    // active or the sweep would find it forever.
    $grant = expiryJobGrant();
    $grant->orderItem->package->forceDelete();

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
    $this->assertDatabaseCount('command_queues', 0);
});

test('a grant whose package was retired still runs its expiry commands', function () {
    // Retiring a package soft-deletes it, and timed grants sold before that keep running. Without
    // withTrashed() on the relation the package reads as gone, the sweep marks the grant EXPIRED
    // and sends nothing, and the buyer keeps the perk they stopped paying for.
    $grant = expiryJobGrant();
    $order = $grant->orderItem->order;
    $grant->orderItem->package->delete();

    runSweep();

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
    expect(CommandQueue::where('tag', 'store')->first()?->parsed_command)
        ->toBe('lp user '.$order->player_username.' parent remove vip');
});

test('a retired package is still named on its grant', function () {
    $grant = expiryJobGrant();
    $name = $grant->orderItem->package->name;
    $grant->orderItem->package->delete();

    expect($grant->fresh()->package?->name)->toBe($name);
});

test('deleting an order item takes its grant with it', function () {
    // Documents why the sweep never has to cope with an orphaned grant: the foreign key
    // cascades, so there is no such row to find.
    $grant = expiryJobGrant();

    StoreOrderItem::whereKey($grant->store_order_item_id)->delete();

    $this->assertDatabaseMissing('store_package_grants', ['id' => $grant->id]);
});

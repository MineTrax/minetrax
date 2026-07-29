<?php

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Jobs\RunCommandQueueJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePackageGrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Server::factory()->create();

    // The queue is sync in tests, so a dispatched command would open a real socket to the
    // factory server and block for the 10s webquery timeout. Every assertion here is about
    // the command_queues rows, which are written before dispatch.
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * @param  array<string, mixed>  $grantAttributes
 */
function grantAdminGrant(array $grantAttributes = [], bool $withExpiryCommand = true): StorePackageGrant
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
        'granted_at' => now()->subDay(),
        'expires_at' => now()->addDays(29),
    ], $grantAttributes));
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.grant.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.grant.index'))->assertStatus(302);
});

test('staff without order read permission are forbidden', function () {
    // Grants are governed by the order permissions rather than one of their own.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.grant.index'))->assertStatus(403);
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.grant.index'))->assertStatus(403);
});

test('superadmin can list grants with their player and package', function () {
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant();

    $this->get(route('admin.store.grant.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreGrant/IndexStoreGrant')
            ->has('grants.data', 1)
            ->where('grants.data.0.player_uuid', $grant->player_uuid)
            ->where('grants.data.0.package.name', $grant->package->name)
            ->where('grants.data.0.order_item.order.uuid', $grant->orderItem->order->uuid)
        );
});

test('grants can be filtered by player username', function () {
    // The username is on the order, two relations away from the grant.
    $this->actingAs(User::whereId(1)->first());
    $wanted = grantAdminGrant();
    $wanted->orderItem->order->update(['player_username' => 'Notch']);
    grantAdminGrant();

    $this->get(route('admin.store.grant.index', ['filter[player_username]' => 'Notch']))
        ->assertInertia(fn ($page) => $page
            ->has('grants.data', 1)
            ->where('grants.data.0.id', $wanted->id)
        );
});

test('grants can be filtered by status', function () {
    $this->actingAs(User::whereId(1)->first());
    grantAdminGrant();
    $revoked = grantAdminGrant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => now()]);

    $this->get(route('admin.store.grant.index', ['filter[status]' => 'revoked']))
        ->assertInertia(fn ($page) => $page
            ->has('grants.data', 1)
            ->where('grants.data.0.id', $revoked->id)
        );
});

test('revoking a grant marks it and runs the expiry commands', function () {
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant();
    $order = $grant->orderItem->order;

    $this->from(route('admin.store.grant.index'))
        ->post(route('admin.store.grant.revoke', $grant->id))
        ->assertRedirect(route('admin.store.grant.index'));

    $grant->refresh();
    expect($grant->status)->toEqual(StorePackageGrantStatus::REVOKED);
    expect($grant->revoked_at)->not->toBeNull();

    $queue = CommandQueue::where('tag', 'store')->first();
    expect($queue)->not->toBeNull('Revoking should queue the expiry commands.');
    expect($queue->parsed_command)->toBe('lp user '.$order->player_username.' parent remove vip');
    $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'expiry']);
});

test('revoking does not give the stock back', function () {
    // The sale still happened. Taking a perk away is not un-selling it, so purchase limits and
    // the sold-out badge are left where they are — unlike a refund, which does restock.
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant();
    $grant->package->update(['sold_count' => 5]);

    $this->post(route('admin.store.grant.revoke', $grant->id));

    expect((int) $grant->package->fresh()->sold_count)->toBe(5);
});

test('revoking an already revoked grant changes nothing', function () {
    $this->actingAs(User::whereId(1)->first());
    $revokedAt = now()->subWeek();
    $grant = grantAdminGrant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => $revokedAt]);

    $this->post(route('admin.store.grant.revoke', $grant->id));

    expect($grant->fresh()->revoked_at->timestamp)->toEqual($revokedAt->timestamp);
    $this->assertDatabaseCount('command_queues', 0);
});

test('a grant with no expiry commands is still revoked', function () {
    // A package that never had removal commands written is a configuration gap, not a reason to
    // leave the grant sitting active.
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant(withExpiryCommand: false);

    $this->post(route('admin.store.grant.revoke', $grant->id));

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
    $this->assertDatabaseCount('command_queues', 0);
});

test('staff without update permission cannot revoke', function () {
    $staff = User::factory()->create();
    $staff->assignRole('moderator');
    $grant = grantAdminGrant();

    $this->actingAs($staff)
        ->post(route('admin.store.grant.revoke', $grant->id))
        ->assertStatus(403);

    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE);
});

test('extending pushes the expiry out from the current date', function () {
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant(['expires_at' => now()->addDays(10)]);
    $expected = $grant->expires_at->copy()->addDays(30);

    $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

    expect($grant->fresh()->expires_at->timestamp)->toEqual($expected->timestamp);
});

test('extending requires a positive number of days', function () {
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant();

    $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 0])
        ->assertSessionHasErrors(['days']);
});

test('a permanent grant cannot be extended', function () {
    // There is no expiry to move, and inventing one would shorten a purchase rather than
    // extending it.
    $this->actingAs(User::whereId(1)->first());
    $grant = grantAdminGrant(['expires_at' => null]);

    $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

    expect($grant->fresh()->expires_at)->toBeNull();
});

test('an expired grant cannot be extended', function () {
    $this->actingAs(User::whereId(1)->first());
    $expiry = now()->subDay();
    $grant = grantAdminGrant(['status' => StorePackageGrantStatus::EXPIRED, 'expires_at' => $expiry]);

    $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

    expect($grant->fresh()->expires_at->timestamp)->toEqual($expiry->timestamp);
    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::EXPIRED);
});

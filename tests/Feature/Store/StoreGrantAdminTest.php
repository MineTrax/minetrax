<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePackageGrant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The cross-order view of what players currently hold, and the two things staff do to a grant by
 * hand: take it back, or push its expiry out.
 */
class StoreGrantAdminTest extends TestCase
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
            'granted_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
        ], $grantAttributes));
    }

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.grant.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.grant.index'))->assertStatus(302);
    }

    public function test_staff_without_order_read_permission_are_forbidden()
    {
        // Grants are governed by the order permissions rather than one of their own.
        $staff = User::factory()->create();
        $staff->assignRole('moderator');

        $this->actingAs($staff)->get(route('admin.store.grant.index'))->assertStatus(403);
    }

    public function test_the_index_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)->get(route('admin.store.grant.index'))->assertStatus(403);
    }

    public function test_superadmin_can_list_grants_with_their_player_and_package()
    {
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant();

        $this->get(route('admin.store.grant.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreGrant/IndexStoreGrant')
                ->has('grants.data', 1)
                ->where('grants.data.0.player_uuid', $grant->player_uuid)
                ->where('grants.data.0.package.name', $grant->package->name)
                ->where('grants.data.0.order_item.order.uuid', $grant->orderItem->order->uuid)
            );
    }

    public function test_grants_can_be_filtered_by_player_username()
    {
        // The username is on the order, two relations away from the grant.
        $this->actingAs(User::whereId(1)->first());
        $wanted = $this->grant();
        $wanted->orderItem->order->update(['player_username' => 'Notch']);
        $this->grant();

        $this->get(route('admin.store.grant.index', ['filter[player_username]' => 'Notch']))
            ->assertInertia(fn ($page) => $page
                ->has('grants.data', 1)
                ->where('grants.data.0.id', $wanted->id)
            );
    }

    public function test_grants_can_be_filtered_by_status()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->grant();
        $revoked = $this->grant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => now()]);

        $this->get(route('admin.store.grant.index', ['filter[status]' => 'revoked']))
            ->assertInertia(fn ($page) => $page
                ->has('grants.data', 1)
                ->where('grants.data.0.id', $revoked->id)
            );
    }

    public function test_revoking_a_grant_marks_it_and_runs_the_expiry_commands()
    {
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant();
        $order = $grant->orderItem->order;

        $this->from(route('admin.store.grant.index'))
            ->post(route('admin.store.grant.revoke', $grant->id))
            ->assertRedirect(route('admin.store.grant.index'));

        $grant->refresh();
        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->status);
        $this->assertNotNull($grant->revoked_at);

        $queue = CommandQueue::where('tag', 'store')->first();
        $this->assertNotNull($queue, 'Revoking should queue the expiry commands.');
        $this->assertSame('lp user '.$order->player_username.' parent remove vip', $queue->parsed_command);
        $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'expiry']);
    }

    public function test_revoking_does_not_give_the_stock_back()
    {
        // The sale still happened. Taking a perk away is not un-selling it, so purchase limits and
        // the sold-out badge are left where they are — unlike a refund, which does restock.
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant();
        $grant->package->update(['sold_count' => 5]);

        $this->post(route('admin.store.grant.revoke', $grant->id));

        $this->assertSame(5, (int) $grant->package->fresh()->sold_count);
    }

    public function test_revoking_an_already_revoked_grant_changes_nothing()
    {
        $this->actingAs(User::whereId(1)->first());
        $revokedAt = now()->subWeek();
        $grant = $this->grant(['status' => StorePackageGrantStatus::REVOKED, 'revoked_at' => $revokedAt]);

        $this->post(route('admin.store.grant.revoke', $grant->id));

        $this->assertEquals($revokedAt->timestamp, $grant->fresh()->revoked_at->timestamp);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_a_grant_with_no_expiry_commands_is_still_revoked()
    {
        // A package that never had removal commands written is a configuration gap, not a reason to
        // leave the grant sitting active.
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant(withExpiryCommand: false);

        $this->post(route('admin.store.grant.revoke', $grant->id));

        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->fresh()->status);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_staff_without_update_permission_cannot_revoke()
    {
        $staff = User::factory()->create();
        $staff->assignRole('moderator');
        $grant = $this->grant();

        $this->actingAs($staff)
            ->post(route('admin.store.grant.revoke', $grant->id))
            ->assertStatus(403);

        $this->assertEquals(StorePackageGrantStatus::ACTIVE, $grant->fresh()->status);
    }

    public function test_extending_pushes_the_expiry_out_from_the_current_date()
    {
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant(['expires_at' => now()->addDays(10)]);
        $expected = $grant->expires_at->copy()->addDays(30);

        $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

        $this->assertEquals($expected->timestamp, $grant->fresh()->expires_at->timestamp);
    }

    public function test_extending_requires_a_positive_number_of_days()
    {
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant();

        $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 0])
            ->assertSessionHasErrors(['days']);
    }

    public function test_a_permanent_grant_cannot_be_extended()
    {
        // There is no expiry to move, and inventing one would shorten a purchase rather than
        // extending it.
        $this->actingAs(User::whereId(1)->first());
        $grant = $this->grant(['expires_at' => null]);

        $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

        $this->assertNull($grant->fresh()->expires_at);
    }

    public function test_an_expired_grant_cannot_be_extended()
    {
        $this->actingAs(User::whereId(1)->first());
        $expiry = now()->subDay();
        $grant = $this->grant(['status' => StorePackageGrantStatus::EXPIRED, 'expires_at' => $expiry]);

        $this->post(route('admin.store.grant.extend', $grant->id), ['days' => 30]);

        $this->assertEquals($expiry->timestamp, $grant->fresh()->expires_at->timestamp);
        $this->assertEquals(StorePackageGrantStatus::EXPIRED, $grant->fresh()->status);
    }
}

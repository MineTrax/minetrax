<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePackageAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'VIP Rank',
            'store_category_id' => null,
            'short_description' => 'Access to VIP perks.',
            'description' => 'A longer description.',
            'price' => 999, // minor units
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
            'requires_login' => false,
            'min_quantity' => 1,
            'max_quantity' => null,
            'stock_limit' => null,
            'player_purchase_limit' => null,
            'purchase_limit_period_days' => null,
            'expiry_duration_days' => null,
            'commands' => [],
        ], $overrides);
    }

    private function commandPayload(array $overrides = []): array
    {
        return array_merge([
            'trigger' => StorePackageCommandTrigger::PURCHASE->value,
            'command' => 'lp user {PLAYER_USERNAME} parent add vip',
            'is_player_online_required' => false,
            'delay_seconds' => 0,
            'servers' => [],
            'is_repeat_per_quantity' => false,
            'sort_order' => 0,
        ], $overrides);
    }

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.package.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.package.index'))->assertStatus(302);
    }

    public function test_admin_can_view_the_package_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        StorePackage::factory()->count(2)->create();

        $this->get(route('admin.store.package.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Admin/StorePackage/IndexStorePackage', false));
    }

    public function test_create_form_only_offers_servers_that_can_receive_commands()
    {
        $this->actingAs(User::whereId(1)->first());
        $reachable = Server::factory()->create();
        Server::factory()->create(['webquery_port' => null]);

        $this->get(route('admin.store.package.create'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StorePackage/CreateStorePackage', false)
                ->has('servers', 1)
                ->where('servers.0.id', $reachable->id)
            );
    }

    public function test_admin_can_create_a_package()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'store_category_id' => $category->id,
        ]))->assertRedirect(route('admin.store.package.index'));

        $this->assertDatabaseHas('store_packages', [
            'name' => 'VIP Rank',
            'slug' => 'vip-rank',
            'price' => 999,
            'store_category_id' => $category->id,
        ]);
    }

    public function test_price_is_stored_as_the_integer_minor_units_it_was_sent_as()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload(['price' => 1999]));

        $this->assertSame(1999, StorePackage::first()->price);
    }

    public function test_price_must_be_an_integer()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload(['price' => '9.99']))
            ->assertSessionHasErrors(['price']);
    }

    public function test_max_quantity_cannot_be_below_min_quantity()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'min_quantity' => 5,
            'max_quantity' => 2,
        ]))->assertSessionHasErrors(['max_quantity']);
    }

    public function test_admin_can_pin_a_command_to_specific_servers()
    {
        $this->actingAs(User::whereId(1)->first());
        $servers = Server::factory()->count(2)->create();

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [$this->commandPayload([
                'servers' => $servers->map(fn ($server) => ['id' => $server->id])->all(),
            ])],
        ]))->assertSessionHasNoErrors();

        $command = StorePackage::first()->commands->first();

        $this->assertCount(2, $command->servers);
        $this->assertFalse($command->is_run_on_all_servers, 'Naming servers is the opposite of running everywhere.');
    }

    /**
     * The account-link convention: an empty picker means every server, so one added to the network
     * later is included without anyone re-editing the package.
     */
    public function test_a_command_with_no_servers_is_recorded_as_running_everywhere()
    {
        $this->actingAs(User::whereId(1)->first());
        Server::factory()->count(2)->create();

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [$this->commandPayload(['servers' => []])],
        ]))->assertSessionHasNoErrors();

        $command = StorePackage::first()->commands->first();

        $this->assertCount(0, $command->servers);
        $this->assertTrue($command->is_run_on_all_servers);
    }

    public function test_editing_a_command_replaces_its_server_list()
    {
        $this->actingAs(User::whereId(1)->first());
        $servers = Server::factory()->count(3)->create();

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [$this->commandPayload([
                'servers' => [['id' => $servers[0]->id], ['id' => $servers[1]->id]],
            ])],
        ]));

        $package = StorePackage::first();
        $command = $package->commands->first();

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'commands' => [$this->commandPayload([
                'id' => $command->id,
                'servers' => [['id' => $servers[2]->id]],
            ])],
        ]))->assertSessionHasNoErrors();

        $this->assertEquals([$servers[2]->id], $command->fresh()->servers->pluck('id')->all());
    }

    public function test_admin_can_create_a_package_with_commands()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [
                $this->commandPayload(),
                $this->commandPayload([
                    'trigger' => StorePackageCommandTrigger::EXPIRY->value,
                    'command' => 'lp user {PLAYER_USERNAME} parent remove vip',
                ]),
            ],
        ]))->assertSessionHasNoErrors();

        $package = StorePackage::first();
        $this->assertCount(2, $package->commands);
        $this->assertCount(1, $package->commandsForTrigger(StorePackageCommandTrigger::PURCHASE)->get());
        $this->assertCount(1, $package->commandsForTrigger(StorePackageCommandTrigger::EXPIRY)->get());
    }

    public function test_command_string_is_required()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [$this->commandPayload(['command' => ''])],
        ]))->assertSessionHasErrors(['commands.0.command']);
    }

    public function test_an_unknown_command_trigger_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'commands' => [$this->commandPayload(['trigger' => 'explode'])],
        ]))->assertSessionHasErrors(['commands.0.trigger']);
    }

    public function test_updating_commands_updates_existing_rows_rather_than_recreating_them()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $command = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'commands' => [$this->commandPayload([
                'id' => $command->id,
                'command' => 'say updated',
            ])],
        ]))->assertSessionHasNoErrors();

        $this->assertCount(1, $package->fresh()->commands);
        $this->assertEquals('say updated', $command->fresh()->command);
        $this->assertEquals($command->id, $package->fresh()->commands->first()->id);
    }

    public function test_commands_removed_from_the_form_are_deleted()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $keep = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
        $drop = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'commands' => [$this->commandPayload(['id' => $keep->id])],
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_package_commands', ['id' => $keep->id]);
        $this->assertDatabaseMissing('store_package_commands', ['id' => $drop->id]);
    }

    public function test_submitting_no_commands_clears_them_all()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        StorePackageCommand::factory()->count(3)->create(['store_package_id' => $package->id]);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'commands' => [],
        ]))->assertSessionHasNoErrors();

        $this->assertCount(0, $package->fresh()->commands);
    }

    public function test_a_failed_update_rolls_back_the_command_reconcile()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $command = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

        // A command belonging to a different package must not be adoptable, and the surrounding
        // transaction must leave the original command set untouched.
        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'commands' => [$this->commandPayload(['id' => 999999])],
        ]))->assertSessionHasErrors(['commands.0.id']);

        $this->assertDatabaseHas('store_package_commands', ['id' => $command->id]);
    }

    public function test_deleting_a_package_soft_deletes_it()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();

        $this->delete(route('admin.store.package.delete', $package->id));

        $this->assertSoftDeleted('store_packages', ['id' => $package->id]);
    }
}

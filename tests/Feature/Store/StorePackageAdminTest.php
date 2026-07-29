<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StoreOrder;
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
            'type' => StorePackageType::MINECRAFT_PACKAGE->value,
            'price' => 999, // minor units
            'discount_bp' => 0,
            'is_pay_what_you_want' => false,
            'pay_what_you_want_max' => null,
            'gift_card_amount' => null,
            'is_gift_card_amount_same_as_price' => false,
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
            'requires_login' => false,
            'is_featured' => false,
            'is_giftable' => false,
            'min_quantity' => 1,
            'max_quantity' => null,
            'player_purchase_limit' => null,
            'player_purchase_limit_period_days' => null,
            'global_purchase_limit' => null,
            'global_purchase_limit_period_days' => null,
            'expiry_duration_days' => null,
            'available_from' => null,
            'available_until' => null,
            'required_packages' => [],
            'required_packages_mode' => StorePackageRequirementMode::ALL->value,
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

    public function test_the_listing_carries_each_packages_category()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create(['name' => 'Ranks']);
        StorePackage::factory()->create(['store_category_id' => $category->id]);
        StorePackage::factory()->create(['store_category_id' => null]);

        $this->get(route('admin.store.package.index'))
            ->assertOk()
            ->assertInertia(function ($page) use ($category) {
                $rows = collect($page->toArray()['props']['packages']['data'])->keyBy('store_category_id');

                $this->assertSame('Ranks', $rows[$category->id]['category']['name']);
                $this->assertNull($rows['']['category'] ?? null, 'An uncategorised package still lists.');
            });
    }

    public function test_the_listing_can_be_filtered_by_category_name()
    {
        $this->actingAs(User::whereId(1)->first());
        $ranks = StoreCategory::factory()->create(['name' => 'Ranks']);
        $coins = StoreCategory::factory()->create(['name' => 'Coins']);
        StorePackage::factory()->create(['store_category_id' => $ranks->id]);
        StorePackage::factory()->count(2)->create(['store_category_id' => $coins->id]);

        $this->get(route('admin.store.package.index', ['filter' => ['category.name' => 'Coins']]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('packages.data', 2));
    }

    /**
     * The dropdown is a multiselect, so what actually arrives is an array of names.
     */
    public function test_the_category_filter_accepts_several_categories_at_once()
    {
        $this->actingAs(User::whereId(1)->first());
        $ranks = StoreCategory::factory()->create(['name' => 'Ranks']);
        $coins = StoreCategory::factory()->create(['name' => 'Coins']);
        $keys = StoreCategory::factory()->create(['name' => 'Keys']);
        StorePackage::factory()->create(['store_category_id' => $ranks->id]);
        StorePackage::factory()->count(2)->create(['store_category_id' => $coins->id]);
        StorePackage::factory()->count(4)->create(['store_category_id' => $keys->id]);

        $this->get(route('admin.store.package.index', ['filter' => ['category.name' => ['Ranks', 'Coins']]]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('packages.data', 3));
    }

    public function test_the_listing_groups_packages_by_category_then_by_sort_order()
    {
        $this->actingAs(User::whereId(1)->first());
        $ranks = StoreCategory::factory()->create(['name' => 'Ranks']);
        $coins = StoreCategory::factory()->create(['name' => 'Coins']);

        // Created interleaved, so the grouping cannot pass by accident on insertion order.
        $rankLast = StorePackage::factory()->create(['store_category_id' => $ranks->id, 'sort_order' => 9]);
        $coinLast = StorePackage::factory()->create(['store_category_id' => $coins->id, 'sort_order' => 1]);
        $rankFirst = StorePackage::factory()->create(['store_category_id' => $ranks->id, 'sort_order' => 5]);
        $coinFirst = StorePackage::factory()->create(['store_category_id' => $coins->id, 'sort_order' => 0]);

        $this->get(route('admin.store.package.index'))
            ->assertOk()
            ->assertInertia(function ($page) use ($rankFirst, $rankLast, $coinFirst, $coinLast) {
                $ids = collect($page->toArray()['props']['packages']['data'])->pluck('id')->all();

                // Ranks together then Coins together, each in the admin's own sort_order.
                $this->assertSame([$rankFirst->id, $rankLast->id, $coinFirst->id, $coinLast->id], $ids);
            });
    }

    public function test_two_packages_sharing_a_sort_order_fall_back_to_id()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();
        $earlier = StorePackage::factory()->create(['store_category_id' => $category->id, 'sort_order' => 0]);
        $later = StorePackage::factory()->create(['store_category_id' => $category->id, 'sort_order' => 0]);

        $this->get(route('admin.store.package.index'))
            ->assertOk()
            ->assertInertia(function ($page) use ($earlier, $later) {
                $ids = collect($page->toArray()['props']['packages']['data'])->pluck('id')->all();

                $this->assertSame([$earlier->id, $later->id], $ids, 'Otherwise the order is whatever MySQL feels like.');
            });
    }

    public function test_an_uncategorised_package_leads_the_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();
        $categorised = StorePackage::factory()->create(['store_category_id' => $category->id]);
        $loose = StorePackage::factory()->create(['store_category_id' => null]);

        $this->get(route('admin.store.package.index'))
            ->assertOk()
            ->assertInertia(function ($page) use ($categorised, $loose) {
                $ids = collect($page->toArray()['props']['packages']['data'])->pluck('id')->all();

                $this->assertSame([$loose->id, $categorised->id], $ids, 'A null category sorts first.');
            });
    }

    public function test_the_listing_offers_every_category_as_a_filter_option()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCategory::factory()->create(['name' => 'Ranks']);
        StoreCategory::factory()->create(['name' => 'Coins']);

        $this->get(route('admin.store.package.index'))
            ->assertOk()
            // Alphabetical, and names rather than ids, because the filter is on category.name.
            ->assertInertia(fn ($page) => $page->where('categoryNames', ['Coins', 'Ranks']));
    }

    /**
     * The edit form has to arrive with the category already chosen, which means the value it binds
     * must match one of the select's own options.
     */
    public function test_the_edit_form_preselects_the_current_category()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();
        $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

        $this->get(route('admin.store.package.edit', $package->id))
            ->assertOk()
            ->assertInertia(function ($page) use ($category) {
                $props = $page->toArray()['props'];

                $this->assertEquals($category->id, $props['storePackage']['store_category_id']);
                $this->assertContains(
                    $category->id,
                    collect($props['categories'])->pluck('id')->all(),
                    'The current category has to be among the options, or nothing can be selected.'
                );
            });
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

    // --- Slugs ------------------------------------------------------------------------------

    public function test_the_slug_is_built_from_the_name_when_none_is_given()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload(['name' => 'Diamond Rank']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_packages', ['name' => 'Diamond Rank', 'slug' => 'diamond-rank']);
    }

    public function test_an_admin_can_choose_the_slug()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'name' => 'Diamond Rank',
            'slug' => 'diamond',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_packages', ['name' => 'Diamond Rank', 'slug' => 'diamond']);
    }

    public function test_a_typed_slug_is_normalised_rather_than_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload([
            'slug' => '  Diamond RANK!! ',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_packages', ['slug' => 'diamond-rank']);
    }

    public function test_a_name_that_slugs_to_nothing_still_produces_a_usable_slug()
    {
        // Str::slug strips a name with no latin characters down to an empty string. Failing
        // `required` there would leave the admin with a form that will not submit.
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.package.store'), $this->validPayload(['name' => '日本語パック']))
            ->assertSessionHasNoErrors();

        $package = StorePackage::firstWhere('name', '日本語パック');

        $this->assertNotNull($package);
        $this->assertMatchesRegularExpression('/^package-[a-z0-9]{8}$/', $package->slug);
    }

    public function test_a_slug_already_used_by_a_live_package_is_refused_on_the_slug_field()
    {
        // On `slug`, because that is now a field the admin can see and fix.
        $this->actingAs(User::whereId(1)->first());
        StorePackage::factory()->create(['slug' => 'diamond-rank']);

        $this->post(route('admin.store.package.store'), $this->validPayload(['slug' => 'diamond-rank']))
            ->assertSessionHasErrors(['slug']);
    }

    public function test_deleting_a_package_releases_its_slug()
    {
        // The whole point: the slug is unique across trashed rows too, so a deleted package would
        // otherwise sit on the name forever.
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

        $this->delete(route('admin.store.package.delete', $package->id));

        $trashed = StorePackage::withTrashed()->find($package->id);

        $this->assertStringStartsWith('deleted-', $trashed->slug);
        // The name is untouched, so the trashed row is still recognisable in the database.
        $this->assertSame('VIP Rank', $trashed->name);
    }

    public function test_a_package_can_be_recreated_under_the_name_of_a_deleted_one()
    {
        $this->actingAs(User::whereId(1)->first());
        $old = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

        $this->delete(route('admin.store.package.delete', $old->id));

        $this->post(route('admin.store.package.store'), $this->validPayload(['name' => 'VIP Rank']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_packages', [
            'name' => 'VIP Rank',
            'slug' => 'vip-rank',
            'deleted_at' => null,
        ]);
    }

    public function test_renaming_a_package_leaves_its_address_alone()
    {
        // Re-deriving the slug on every save meant a rename silently broke every link anybody had
        // posted to the package.
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => 'Renamed Rank',
            'slug' => null,
        ]))->assertSessionHasNoErrors();

        $package->refresh();

        $this->assertSame('Renamed Rank', $package->name);
        $this->assertSame('vip-rank', $package->slug);
    }

    public function test_an_admin_can_change_the_slug_on_an_existing_package()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'slug' => 'vip',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('vip', $package->fresh()->slug);
    }

    public function test_keeping_its_own_slug_on_update_is_not_a_duplicate()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'slug' => 'vip-rank',
        ]))->assertSessionHasNoErrors();
    }

    public function test_a_slug_belonging_to_another_live_package_is_refused_on_update()
    {
        $this->actingAs(User::whereId(1)->first());
        StorePackage::factory()->create(['slug' => 'taken']);
        $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

        $this->put(route('admin.store.package.update', $package->id), $this->validPayload([
            'name' => $package->name,
            'slug' => 'taken',
        ]))->assertSessionHasErrors(['slug']);

        $this->assertSame('vip-rank', $package->fresh()->slug);
    }

    public function test_a_deleted_packages_orders_stay_readable()
    {
        // Freeing the slug must not cost the audit trail: order items snapshot the name, so nothing
        // about a past purchase depends on the slug.
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);
        $order = StoreOrder::factory()->completed()->create();
        $item = $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 1,
            'unit_price_original' => 999,
            'unit_price' => 999,
            'total' => 999,
        ]);

        $this->delete(route('admin.store.package.delete', $package->id));

        $item->refresh();
        $this->assertSame('VIP Rank', $item->package_name);
        $this->assertSame($package->id, $item->store_package_id);
    }
}

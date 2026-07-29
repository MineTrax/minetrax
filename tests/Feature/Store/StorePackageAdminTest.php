<?php

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

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

function packageAdminValidPayload(array $overrides = []): array
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

function commandPayload(array $overrides = []): array
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

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.package.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.package.index'))->assertStatus(302);
});

test('admin can view the package listing', function () {
    $this->actingAs(User::whereId(1)->first());
    StorePackage::factory()->count(2)->create();

    $this->get(route('admin.store.package.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Admin/StorePackage/IndexStorePackage', false));
});

test('the listing carries each packages category', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create(['name' => 'Ranks']);
    StorePackage::factory()->create(['store_category_id' => $category->id]);
    StorePackage::factory()->create(['store_category_id' => null]);

    $this->get(route('admin.store.package.index'))
        ->assertOk()
        ->assertInertia(function ($page) use ($category) {
            $rows = collect($page->toArray()['props']['packages']['data'])->keyBy('store_category_id');

            expect($rows[$category->id]['category']['name'])->toBe('Ranks');
            expect($rows['']['category'] ?? null)->toBeNull('An uncategorised package still lists.');
        });
});

test('the listing can be filtered by category name', function () {
    $this->actingAs(User::whereId(1)->first());
    $ranks = StoreCategory::factory()->create(['name' => 'Ranks']);
    $coins = StoreCategory::factory()->create(['name' => 'Coins']);
    StorePackage::factory()->create(['store_category_id' => $ranks->id]);
    StorePackage::factory()->count(2)->create(['store_category_id' => $coins->id]);

    $this->get(route('admin.store.package.index', ['filter' => ['category.name' => 'Coins']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('packages.data', 2));
});

test('the category filter accepts several categories at once', function () {
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
});

test('the listing groups packages by category then by sort order', function () {
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
            expect($ids)->toBe([$rankFirst->id, $rankLast->id, $coinFirst->id, $coinLast->id]);
        });
});

test('two packages sharing a sort order fall back to id', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();
    $earlier = StorePackage::factory()->create(['store_category_id' => $category->id, 'sort_order' => 0]);
    $later = StorePackage::factory()->create(['store_category_id' => $category->id, 'sort_order' => 0]);

    $this->get(route('admin.store.package.index'))
        ->assertOk()
        ->assertInertia(function ($page) use ($earlier, $later) {
            $ids = collect($page->toArray()['props']['packages']['data'])->pluck('id')->all();

            expect($ids)->toBe([$earlier->id, $later->id], 'Otherwise the order is whatever MySQL feels like.');
        });
});

test('an uncategorised package leads the listing', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();
    $categorised = StorePackage::factory()->create(['store_category_id' => $category->id]);
    $loose = StorePackage::factory()->create(['store_category_id' => null]);

    $this->get(route('admin.store.package.index'))
        ->assertOk()
        ->assertInertia(function ($page) use ($categorised, $loose) {
            $ids = collect($page->toArray()['props']['packages']['data'])->pluck('id')->all();

            expect($ids)->toBe([$loose->id, $categorised->id], 'A null category sorts first.');
        });
});

test('the listing offers every category as a filter option', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCategory::factory()->create(['name' => 'Ranks']);
    StoreCategory::factory()->create(['name' => 'Coins']);

    $this->get(route('admin.store.package.index'))
        ->assertOk()
        // Alphabetical, and names rather than ids, because the filter is on category.name.
        ->assertInertia(fn ($page) => $page->where('categoryNames', ['Coins', 'Ranks']));
});

test('the edit form preselects the current category', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();
    $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

    $this->get(route('admin.store.package.edit', $package->id))
        ->assertOk()
        ->assertInertia(function ($page) use ($category) {
            $props = $page->toArray()['props'];

            expect($props['storePackage']['store_category_id'])->toEqual($category->id);
            $this->assertContains(
                $category->id,
                collect($props['categories'])->pluck('id')->all(),
                'The current category has to be among the options, or nothing can be selected.'
            );
        });
});

test('create form only offers servers that can receive commands', function () {
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
});

test('admin can create a package', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'store_category_id' => $category->id,
    ]))->assertRedirect(route('admin.store.package.index'));

    $this->assertDatabaseHas('store_packages', [
        'name' => 'VIP Rank',
        'slug' => 'vip-rank',
        'price' => 999,
        'store_category_id' => $category->id,
    ]);
});

test('price is stored as the integer minor units it was sent as', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['price' => 1999]));

    expect(StorePackage::first()->price)->toBe(1999);
});

test('price must be an integer', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['price' => '9.99']))
        ->assertSessionHasErrors(['price']);
});

test('max quantity cannot be below min quantity', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'min_quantity' => 5,
        'max_quantity' => 2,
    ]))->assertSessionHasErrors(['max_quantity']);
});

test('admin can pin a command to specific servers', function () {
    $this->actingAs(User::whereId(1)->first());
    $servers = Server::factory()->count(2)->create();

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [commandPayload([
            'servers' => $servers->map(fn ($server) => ['id' => $server->id])->all(),
        ])],
    ]))->assertSessionHasNoErrors();

    $command = StorePackage::first()->commands->first();

    expect($command->servers)->toHaveCount(2);
    expect($command->is_run_on_all_servers)->toBeFalse('Naming servers is the opposite of running everywhere.');
});

test('a command with no servers is recorded as running everywhere', function () {
    $this->actingAs(User::whereId(1)->first());
    Server::factory()->count(2)->create();

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [commandPayload(['servers' => []])],
    ]))->assertSessionHasNoErrors();

    $command = StorePackage::first()->commands->first();

    expect($command->servers)->toHaveCount(0);
    expect($command->is_run_on_all_servers)->toBeTrue();
});

test('editing a command replaces its server list', function () {
    $this->actingAs(User::whereId(1)->first());
    $servers = Server::factory()->count(3)->create();

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [commandPayload([
            'servers' => [['id' => $servers[0]->id], ['id' => $servers[1]->id]],
        ])],
    ]));

    $package = StorePackage::first();
    $command = $package->commands->first();

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'commands' => [commandPayload([
            'id' => $command->id,
            'servers' => [['id' => $servers[2]->id]],
        ])],
    ]))->assertSessionHasNoErrors();

    expect($command->fresh()->servers->pluck('id')->all())->toEqual([$servers[2]->id]);
});

test('admin can create a package with commands', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [
            commandPayload(),
            commandPayload([
                'trigger' => StorePackageCommandTrigger::EXPIRY->value,
                'command' => 'lp user {PLAYER_USERNAME} parent remove vip',
            ]),
        ],
    ]))->assertSessionHasNoErrors();

    $package = StorePackage::first();
    expect($package->commands)->toHaveCount(2);
    expect($package->commandsForTrigger(StorePackageCommandTrigger::PURCHASE)->get())->toHaveCount(1);
    expect($package->commandsForTrigger(StorePackageCommandTrigger::EXPIRY)->get())->toHaveCount(1);
});

test('command string is required', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [commandPayload(['command' => ''])],
    ]))->assertSessionHasErrors(['commands.0.command']);
});

test('an unknown command trigger is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'commands' => [commandPayload(['trigger' => 'explode'])],
    ]))->assertSessionHasErrors(['commands.0.trigger']);
});

test('updating commands updates existing rows rather than recreating them', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $command = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'commands' => [commandPayload([
            'id' => $command->id,
            'command' => 'say updated',
        ])],
    ]))->assertSessionHasNoErrors();

    expect($package->fresh()->commands)->toHaveCount(1);
    expect($command->fresh()->command)->toEqual('say updated');
    expect($package->fresh()->commands->first()->id)->toEqual($command->id);
});

test('commands removed from the form are deleted', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $keep = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
    $drop = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'commands' => [commandPayload(['id' => $keep->id])],
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_package_commands', ['id' => $keep->id]);
    $this->assertDatabaseMissing('store_package_commands', ['id' => $drop->id]);
});

test('submitting no commands clears them all', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    StorePackageCommand::factory()->count(3)->create(['store_package_id' => $package->id]);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'commands' => [],
    ]))->assertSessionHasNoErrors();

    expect($package->fresh()->commands)->toHaveCount(0);
});

test('a failed update rolls back the command reconcile', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $command = StorePackageCommand::factory()->create(['store_package_id' => $package->id]);

    // A command belonging to a different package must not be adoptable, and the surrounding
    // transaction must leave the original command set untouched.
    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'commands' => [commandPayload(['id' => 999999])],
    ]))->assertSessionHasErrors(['commands.0.id']);

    $this->assertDatabaseHas('store_package_commands', ['id' => $command->id]);
});

test('deleting a package soft deletes it', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();

    $this->delete(route('admin.store.package.delete', $package->id));

    $this->assertSoftDeleted('store_packages', ['id' => $package->id]);
});

test('the slug is built from the name when none is given', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['name' => 'Diamond Rank']))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_packages', ['name' => 'Diamond Rank', 'slug' => 'diamond-rank']);
});

test('an admin can choose the slug', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'name' => 'Diamond Rank',
        'slug' => 'diamond',
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_packages', ['name' => 'Diamond Rank', 'slug' => 'diamond']);
});

test('a typed slug is normalised rather than rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload([
        'slug' => '  Diamond RANK!! ',
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_packages', ['slug' => 'diamond-rank']);
});

test('a name that slugs to nothing still produces a usable slug', function () {
    // Str::slug strips a name with no latin characters down to an empty string. Failing
    // `required` there would leave the admin with a form that will not submit.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['name' => '日本語パック']))
        ->assertSessionHasNoErrors();

    $package = StorePackage::firstWhere('name', '日本語パック');

    expect($package)->not->toBeNull();
    expect($package->slug)->toMatch('/^package-[a-z0-9]{8}$/');
});

test('a slug already used by a live package is refused on the slug field', function () {
    // On `slug`, because that is now a field the admin can see and fix.
    $this->actingAs(User::whereId(1)->first());
    StorePackage::factory()->create(['slug' => 'diamond-rank']);

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['slug' => 'diamond-rank']))
        ->assertSessionHasErrors(['slug']);
});

test('deleting a package releases its slug', function () {
    // The whole point: the slug is unique across trashed rows too, so a deleted package would
    // otherwise sit on the name forever.
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

    $this->delete(route('admin.store.package.delete', $package->id));

    $trashed = StorePackage::withTrashed()->find($package->id);

    expect($trashed->slug)->toStartWith('deleted-');

    // The name is untouched, so the trashed row is still recognisable in the database.
    expect($trashed->name)->toBe('VIP Rank');
});

test('a package can be recreated under the name of a deleted one', function () {
    $this->actingAs(User::whereId(1)->first());
    $old = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

    $this->delete(route('admin.store.package.delete', $old->id));

    $this->post(route('admin.store.package.store'), packageAdminValidPayload(['name' => 'VIP Rank']))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_packages', [
        'name' => 'VIP Rank',
        'slug' => 'vip-rank',
        'deleted_at' => null,
    ]);
});

test('renaming a package leaves its address alone', function () {
    // Re-deriving the slug on every save meant a rename silently broke every link anybody had
    // posted to the package.
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'slug' => 'vip-rank']);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => 'Renamed Rank',
        'slug' => null,
    ]))->assertSessionHasNoErrors();

    $package->refresh();

    expect($package->name)->toBe('Renamed Rank');
    expect($package->slug)->toBe('vip-rank');
});

test('an admin can change the slug on an existing package', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'slug' => 'vip',
    ]))->assertSessionHasNoErrors();

    expect($package->fresh()->slug)->toBe('vip');
});

test('keeping its own slug on update is not a duplicate', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'slug' => 'vip-rank',
    ]))->assertSessionHasNoErrors();
});

test('a slug belonging to another live package is refused on update', function () {
    $this->actingAs(User::whereId(1)->first());
    StorePackage::factory()->create(['slug' => 'taken']);
    $package = StorePackage::factory()->create(['slug' => 'vip-rank']);

    $this->put(route('admin.store.package.update', $package->id), packageAdminValidPayload([
        'name' => $package->name,
        'slug' => 'taken',
    ]))->assertSessionHasErrors(['slug']);

    expect($package->fresh()->slug)->toBe('vip-rank');
});

test('a deleted packages orders stay readable', function () {
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
    expect($item->package_name)->toBe('VIP Rank');
    expect($item->store_package_id)->toBe($package->id);
});

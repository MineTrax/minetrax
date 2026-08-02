<?php

use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDiscountType;
use App\Enums\StoreSaleScope;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StoreCommand;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Services\StorePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function saleAdminValidPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Summer Sale',
        'discount_type' => StoreDiscountType::PERCENT->value,
        'discount_value' => 2500,
        'starts_at' => null,
        'ends_at' => null,
        'is_enabled' => true,
        'scope_type' => StoreSaleScope::ALL->value,
        'min_basket_amount' => null,
        'packages' => [],
        'categories' => [],
        'commands' => [],
    ], $overrides);
}

/**
 * One row of the sale's command repeater, in the shape the Vue form submits.
 *
 * @param  array<int, int>  $serverIds
 * @param  array<int, int>  $packageIds
 */
function saleCommandPayload(array $overrides = [], array $serverIds = [], array $packageIds = []): array
{
    return array_merge([
        'trigger' => StoreCommandTrigger::PURCHASE->value,
        'command' => 'give {PLAYER_USERNAME} coins 100',
        'is_player_online_required' => false,
        'delay_seconds' => 0,
        'is_repeat_per_quantity' => false,
        'sort_order' => 0,
        'servers' => array_map(fn (int $id) => ['id' => $id], $serverIds),
        'packages' => array_map(fn (int $id) => ['id' => $id], $packageIds),
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.sale.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.sale.index'))->assertStatus(302);
});

test('staff without the permission are forbidden', function () {
    // Moderator is staff but is granted no store permissions by RoleSeeder.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.sale.index'))->assertStatus(403);
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.sale.index'))->assertStatus(403);
});

test('superadmin can list sales', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreSale::factory()->create(['name' => 'Listed']);

    $this->get(route('admin.store.sale.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreSale/IndexStoreSale')
            ->has('sales.data', 1)
        );
});

test('the listing reports whether a sale is running now', function () {
    // Enabled is not the same as running: a sale can be switched on but out of its window, and
    // the dates alone do not say which.
    $this->actingAs(User::whereId(1)->first());
    StoreSale::factory()->create(['name' => 'Live']);
    StoreSale::factory()->upcoming()->create(['name' => 'Later']);
    StoreSale::factory()->create(['name' => 'Off', 'is_enabled' => false]);

    $this->get(route('admin.store.sale.index'))
        ->assertInertia(function ($page) {
            $byName = collect($page->toArray()['props']['sales']['data'])->keyBy('name');

            expect($byName['Live']['is_running'])->toBeTrue();
            expect($byName['Later']['is_running'])->toBeFalse();
            expect($byName['Off']['is_running'])->toBeFalse();
        });
});

test('admin can create a percentage sale', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload())
        ->assertRedirect(route('admin.store.sale.index'));

    $this->assertDatabaseHas('store_sales', [
        'name' => 'Summer Sale',
        'discount_type' => 'percent',
        'discount_value' => 2500,
        'is_enabled' => true,
    ]);
});

test('a percentage above one hundred is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload(['discount_value' => 10001]))
        ->assertSessionHasErrors(['discount_value']);
});

test('a fixed sale may exceed ten thousand minor units', function () {
    // The percentage cap is not a cap on money: a $150 sale is 15000 minor units.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'discount_type' => StoreDiscountType::FIXED->value,
        'discount_value' => 15000,
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_sales', ['discount_type' => 'fixed', 'discount_value' => 15000]);
});

test('an end date before the start is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'starts_at' => now()->addWeek()->toDateTimeString(),
        'ends_at' => now()->toDateTimeString(),
    ]))->assertSessionHasErrors(['ends_at']);
});

test('scope rows are written for the chosen mode only', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $category = StoreCategory::factory()->create();

    // A package-scoped sale ignores whatever the category picker was left holding: the form shows
    // one picker at a time, so a hidden second selection would be a scope nobody could see.
    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'scope_type' => StoreSaleScope::PACKAGES->value,
        'packages' => [$package->id],
        'categories' => [$category->id],
    ]))->assertSessionHasNoErrors();

    $sale = StoreSale::firstWhere('name', 'Summer Sale');

    $this->assertDatabaseHas('store_saleables', [
        'store_sale_id' => $sale->id,
        'saleable_type' => StorePackage::class,
        'saleable_id' => $package->id,
    ]);
    $this->assertDatabaseMissing('store_saleables', [
        'store_sale_id' => $sale->id,
        'saleable_type' => StoreCategory::class,
    ]);
});

test('a category scoped sale writes only category rows', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'scope_type' => StoreSaleScope::CATEGORIES->value,
        'categories' => [$category->id],
    ]))->assertSessionHasNoErrors();

    $sale = StoreSale::firstWhere('name', 'Summer Sale');

    $this->assertDatabaseHas('store_saleables', [
        'store_sale_id' => $sale->id,
        'saleable_type' => StoreCategory::class,
        'saleable_id' => $category->id,
    ]);
    expect($sale->saleables()->count())->toBe(1);
});

test('switching a sale back to store wide drops its scope rows', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->forPackages()->create();
    $sale->saleables()->create([
        'saleable_type' => StorePackage::class,
        'saleable_id' => $package->id,
    ]);

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => $sale->name,
        'scope_type' => StoreSaleScope::ALL->value,
    ]))->assertSessionHasNoErrors();

    expect($sale->fresh()->saleables()->count())->toBe(0);
    expect($sale->fresh()->scope_type)->toEqual(StoreSaleScope::ALL);
});

test('a scoped sale must name at least one target', function () {
    // The old behaviour read an empty picker as store-wide, so emptying it quietly discounted the
    // whole catalogue. Naming nothing is now a half-finished form, not a request.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'scope_type' => StoreSaleScope::PACKAGES->value,
        'packages' => [],
    ]))->assertSessionHasErrors(['packages']);

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'scope_type' => StoreSaleScope::CATEGORIES->value,
        'categories' => [],
    ]))->assertSessionHasErrors(['categories']);
});

test('the edit page preselects the current scope', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();
    $sale = StoreSale::factory()->create();
    $sale->saleables()->create([
        'saleable_type' => StoreCategory::class,
        'saleable_id' => $category->id,
    ]);

    $this->get(route('admin.store.sale.edit', $sale->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreSale/EditStoreSale')
            ->where('selectedCategories', [$category->id])
            ->where('selectedPackages', [])
        );
});

test('admin can update a sale', function () {
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->create(['name' => 'Old Name']);

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => 'New Name',
        'discount_value' => 500,
        'is_enabled' => false,
    ]))->assertRedirect(route('admin.store.sale.index'));

    $this->assertDatabaseHas('store_sales', [
        'id' => $sale->id,
        'name' => 'New Name',
        'discount_value' => 500,
        'is_enabled' => false,
    ]);
});

test('admin can delete a sale', function () {
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->create();

    $this->delete(route('admin.store.sale.delete', $sale->id))
        ->assertRedirect(route('admin.store.sale.index'));

    // Soft, so the orders it priced keep resolving their refund and expiry commands through it.
    $this->assertSoftDeleted('store_sales', ['id' => $sale->id]);
});

test('a deleted sale discounts nothing and leaves the listing', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['price' => 2000]);
    $sale = StoreSale::factory()->create();

    $this->delete(route('admin.store.sale.delete', $sale->id));

    $quote = app(StorePricingService::class)->quote([['package' => $package->fresh(), 'quantity' => 1]]);
    expect($quote['items'][0]['unit_price'])->toEqual(2000);
    expect($quote['items'][0]['sale_name'])->toBeNull();

    $this->get(route('admin.store.sale.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->where('sales.data', []));
});

test('a created sale discounts the storefront immediately', function () {
    // The point of the CRUD: the pricing service reads sales live, so nothing has to be
    // rebuilt or scheduled for a new sale to take effect.
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['price' => 2000]);

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'scope_type' => StoreSaleScope::PACKAGES->value,
        'packages' => [$package->id],
    ]))->assertSessionHasNoErrors();

    $quote = app(StorePricingService::class)->quote([['package' => $package->fresh(), 'quantity' => 1]]);

    expect($quote['items'][0]['unit_price'])->toEqual(1500);
    expect($quote['items'][0]['sale_name'])->toEqual('Summer Sale');
});

test('a fixed sale amount converts into the quoted currency', function () {
    // The sale amount is held in the base currency. Applied raw against a JPY price it would
    // take ¥5 off a ¥3000 package instead of the ¥750 that $5 is worth.
    $jpy = StoreCurrency::factory()->zeroDecimal()->create();
    // 1 USD = 150 JPY, no minor unit
    $package = StorePackage::factory()->create(['price' => 2000]);
    StoreSale::factory()->fixed(500)->create(['name' => 'Five Off']);

    $quote = app(StorePricingService::class)->quote([['package' => $package, 'quantity' => 1]], $jpy);

    // $20.00 → ¥3000, less $5.00 → ¥750.
    expect($quote['items'][0]['unit_price_original'])->toEqual(3000);
    expect($quote['items'][0]['unit_price_original'] - $quote['items'][0]['unit_price'])->toEqual(750);
    expect($quote['items'][0]['sale_name'])->toEqual('Five Off');
});

test('a fixed sale never takes a line below zero', function () {
    $this->baseCurrency();
    $package = StorePackage::factory()->create(['price' => 300]);
    StoreSale::factory()->fixed(5000)->create();

    $quote = app(StorePricingService::class)->quote([['package' => $package, 'quantity' => 1]]);

    expect($quote['items'][0]['unit_price'])->toEqual(0);
    expect($quote['total'])->toEqual(0);
});

test('admin can set a minimum cart total', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'min_basket_amount' => 5000,
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_sales', [
        'name' => 'Summer Sale',
        'min_basket_amount' => 5000,
    ]);
});

test('a zero minimum is rejected', function () {
    // Indistinguishable from no minimum, and it would hang a "spend $0.00 to unlock" note on
    // every card that qualifies.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'min_basket_amount' => 0,
    ]))->assertSessionHasErrors(['min_basket_amount']);
});

test('the edit page returns the stored minimum', function () {
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->withMinimum(5000)->create();

    $this->get(route('admin.store.sale.edit', $sale->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->where('storeSale.min_basket_amount', 5000));
});

test('a sale with a minimum discounts nothing until the cart reaches it', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['price' => 2000]);

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'min_basket_amount' => 4000,
    ]))->assertSessionHasNoErrors();

    $pricing = app(StorePricingService::class);

    $one = $pricing->quote([['package' => $package->fresh(), 'quantity' => 1]]);
    expect($one['items'][0]['unit_price'])->toEqual(2000);
    expect($one['items'][0]['sale_name'])->toBeNull();

    $two = $pricing->quote([['package' => $package->fresh(), 'quantity' => 2]]);
    expect($two['items'][0]['unit_price'])->toEqual(1500);
    expect($two['items'][0]['sale_name'])->toEqual('Summer Sale');
});

test('admin can attach commands to a sale', function () {
    $this->actingAs(User::whereId(1)->first());
    $server = Server::factory()->create();
    $package = StorePackage::factory()->create();

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'commands' => [
            saleCommandPayload(['command' => 'give {PLAYER_USERNAME} coins 100'], [$server->id], [$package->id]),
        ],
    ]))->assertSessionHasNoErrors();

    $sale = StoreSale::firstWhere('name', 'Summer Sale');
    $command = $sale->commands()->first();

    // Owned by the sale and by nothing else. Every owner shares one table, and the morph makes
    // "exactly one owner" a property of the schema rather than something a check has to enforce.
    expect($command->commandable_type)->toBe(StoreSale::class);
    expect($command->commandable_id)->toBe($sale->id);
    expect($command->command)->toEqual('give {PLAYER_USERNAME} coins 100');
    expect($command->is_run_on_all_servers)->toBeFalse();
    expect($command->is_run_on_all_packages)->toBeFalse();
    expect($command->servers->pluck('id')->all())->toEqual([$server->id]);
    expect($command->packages->pluck('id')->all())->toEqual([$package->id]);
});

test('leaving the command pickers empty records the run on all flags', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'commands' => [saleCommandPayload()],
    ]))->assertSessionHasNoErrors();

    $command = StoreSale::firstWhere('name', 'Summer Sale')->commands()->first();

    expect($command->is_run_on_all_servers)->toBeTrue();
    expect($command->is_run_on_all_packages)->toBeTrue();
});

test('editing removes commands the form no longer lists', function () {
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->create();
    $kept = StoreCommand::factory()->forSale($sale)->create(['command' => 'keep me']);
    $dropped = StoreCommand::factory()->forSale($sale)->create(['command' => 'drop me']);

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => $sale->name,
        'commands' => [saleCommandPayload(['id' => $kept->id, 'command' => 'keep me still'])],
    ]))->assertSessionHasNoErrors();

    expect($sale->fresh()->commands()->pluck('command')->all())->toEqual(['keep me still']);
    $this->assertDatabaseMissing('store_commands', ['id' => $dropped->id]);
});

test('saving one sale never touches another sale or a package', function () {
    // The two kinds share a table, so the scoping in syncCommands is what stops a save reaching
    // across into commands it does not own.
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->create();
    $otherSale = StoreSale::factory()->create();
    $package = StorePackage::factory()->create();

    $otherCommand = StoreCommand::factory()->forSale($otherSale)->create();
    $packageCommand = StoreCommand::factory()->forOwner($package)->create();

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => $sale->name,
        'commands' => [saleCommandPayload()],
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_commands', ['id' => $otherCommand->id]);
    $this->assertDatabaseHas('store_commands', ['id' => $packageCommand->id]);
    expect($sale->fresh()->commands()->count())->toBe(1);
});

test('a forged command id belonging to another sale is not stolen', function () {
    $this->actingAs(User::whereId(1)->first());
    $sale = StoreSale::factory()->create();
    $otherSale = StoreSale::factory()->create();
    $victim = StoreCommand::factory()->forSale($otherSale)->create(['command' => 'untouched']);

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => $sale->name,
        'commands' => [saleCommandPayload(['id' => $victim->id, 'command' => 'stolen'])],
    ]))->assertSessionHasNoErrors();

    expect($victim->fresh()->command)->toEqual('untouched');
    expect($victim->fresh()->commandable_id)->toBe($otherSale->id);
    expect($victim->fresh()->commandable_type)->toBe(StoreSale::class);
    // Falls through to a create on the sale actually being edited.
    expect($sale->fresh()->commands()->count())->toBe(1);
});

test('the edit page hands back commands with their servers and packages', function () {
    $this->actingAs(User::whereId(1)->first());
    $server = Server::factory()->create();
    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->create();
    $command = StoreCommand::factory()->forSale($sale)->create(['is_run_on_all_servers' => false]);
    $command->servers()->sync([$server->id]);
    $command->packages()->sync([$package->id]);

    $this->get(route('admin.store.sale.edit', $sale->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreSale/EditStoreSale')
            ->where('storeSale.commands.0.id', $command->id)
            ->where('storeSale.commands.0.servers.0.id', $server->id)
            ->where('storeSale.commands.0.packages.0.id', $package->id)
        );
});

test('an unknown command trigger is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'commands' => [saleCommandPayload(['trigger' => 'not-a-trigger'])],
    ]))->assertSessionHasErrors(['commands.0.trigger']);
});

test('a command with no text is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'commands' => [saleCommandPayload(['command' => ''])],
    ]))->assertSessionHasErrors(['commands.0.command']);
});

test('an unknown package on a command is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'commands' => [saleCommandPayload([], [], [99999])],
    ]))->assertSessionHasErrors(['commands.0.packages.0.id']);
});

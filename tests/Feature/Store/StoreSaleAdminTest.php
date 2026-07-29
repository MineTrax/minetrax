<?php

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
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
        'packages' => [],
        'categories' => [],
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

test('scope rows are written for packages and categories', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $category = StoreCategory::factory()->create();

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
        'packages' => [$package->id],
        'categories' => [$category->id],
    ]))->assertSessionHasNoErrors();

    $sale = StoreSale::firstWhere('name', 'Summer Sale');

    $this->assertDatabaseHas('store_saleables', [
        'store_sale_id' => $sale->id,
        'saleable_type' => StorePackage::class,
        'saleable_id' => $package->id,
    ]);
    $this->assertDatabaseHas('store_saleables', [
        'store_sale_id' => $sale->id,
        'saleable_type' => StoreCategory::class,
        'saleable_id' => $category->id,
    ]);
});

test('clearing the scope makes the sale store wide again', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->create();
    $sale->saleables()->create([
        'saleable_type' => StorePackage::class,
        'saleable_id' => $package->id,
    ]);

    $this->put(route('admin.store.sale.update', $sale->id), saleAdminValidPayload([
        'name' => $sale->name,
        'packages' => [],
        'categories' => [],
    ]))->assertSessionHasNoErrors();

    expect($sale->fresh()->saleables()->count())->toBe(0);
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

    $this->assertDatabaseMissing('store_sales', ['id' => $sale->id]);
});

test('a created sale discounts the storefront immediately', function () {
    // The point of the CRUD: the pricing service reads sales live, so nothing has to be
    // rebuilt or scheduled for a new sale to take effect.
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['price' => 2000]);

    $this->post(route('admin.store.sale.store'), saleAdminValidPayload([
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

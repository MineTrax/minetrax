<?php

use App\Enums\StorePriceRounding;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

function currencyAdminValidPayload(array $overrides = []): array
{
    return array_merge([
        'code' => 'EUR',
        'name' => 'Euro',
        'symbol' => '€',
        'symbol_position' => 'suffix',
        'exponent' => 2,
        'rate_to_base' => 0.92,
        'is_enabled' => true,
        'price_rounding' => StorePriceRounding::NONE->value,
        'country_codes' => ['DE', 'FR'],
        'sort_order' => 0,
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.currency.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.currency.index'))->assertStatus(302);
});

test('admin can create a currency', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload())
        ->assertRedirect(route('admin.store.currency.index'));

    $this->assertDatabaseHas('store_currencies', ['code' => 'EUR', 'exponent' => 2, 'is_base' => false]);
    expect(StoreCurrency::where('code', 'EUR')->first()->country_codes)->toEqual(['DE', 'FR']);
});

test('currency code is uppercased and must be three letters', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload(['code' => 'eur']));
    $this->assertDatabaseHas('store_currencies', ['code' => 'EUR']);

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload(['code' => 'EURO']))
        ->assertSessionHasErrors(['code']);
});

test('duplicate currency code is rejected', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCurrency::factory()->create(['code' => 'EUR']);

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload())
        ->assertSessionHasErrors(['code']);
});

test('exponent is constrained to the range iso uses', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload(['exponent' => 9]))
        ->assertSessionHasErrors(['exponent']);
});

test('rate must be positive', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.currency.store'), currencyAdminValidPayload(['rate_to_base' => 0]))
        ->assertSessionHasErrors(['rate_to_base']);
});

test('the base currency cannot be disabled or re rated', function () {
    $this->actingAs(User::whereId(1)->first());
    $base = $this->baseCurrency();

    $this->put(route('admin.store.currency.update', $base->id), currencyAdminValidPayload([
        'code' => $base->code,
        'is_enabled' => false,
    ]))->assertSessionHasErrors(['is_enabled']);

    $this->put(route('admin.store.currency.update', $base->id), currencyAdminValidPayload([
        'code' => $base->code,
        'rate_to_base' => 2,
    ]))->assertSessionHasErrors(['rate_to_base']);
});

test('the base currency cannot be deleted', function () {
    $this->actingAs(User::whereId(1)->first());
    $base = $this->baseCurrency();

    $this->delete(route('admin.store.currency.delete', $base->id));

    $this->assertDatabaseHas('store_currencies', ['id' => $base->id]);
});

test('a currency with orders cannot be deleted', function () {
    $this->actingAs(User::whereId(1)->first());
    $currency = StoreCurrency::factory()->create(['code' => 'EUR']);
    StoreOrder::factory()->create(['currency' => 'EUR']);

    $this->delete(route('admin.store.currency.delete', $currency->id));

    $this->assertDatabaseHas('store_currencies', ['id' => $currency->id]);
});

test('an unused currency can be deleted', function () {
    $this->actingAs(User::whereId(1)->first());
    $currency = StoreCurrency::factory()->create(['code' => 'EUR']);

    $this->delete(route('admin.store.currency.delete', $currency->id));

    $this->assertDatabaseMissing('store_currencies', ['id' => $currency->id]);
});

test('base currency can be changed while no orders exist', function () {
    $this->actingAs(User::whereId(1)->first());
    $base = $this->baseCurrency();
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.92]);

    $this->post(route('admin.store.currency.make-base', $euro->id));

    expect($euro->fresh()->is_base)->toBeTrue();
    expect($base->fresh()->is_base)->toBeFalse();
    expect((float) $euro->fresh()->rate_to_base)->toEqual(1);
});

test('base currency is locked once an order exists', function () {
    // Historical base_total values were computed against the current base, so moving it
    // would silently rewrite past revenue.
    $this->actingAs(User::whereId(1)->first());
    $base = $this->baseCurrency();
    $euro = StoreCurrency::factory()->create(['code' => 'EUR']);
    StoreOrder::factory()->create();

    $this->post(route('admin.store.currency.make-base', $euro->id));

    expect($euro->fresh()->is_base)->toBeFalse();
    expect($base->fresh()->is_base)->toBeTrue();
});

test('admin can set per package price overrides', function () {
    $this->actingAs(User::whereId(1)->first());
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);

    $this->put(route('admin.store.package.update', $package->id), [
        'name' => $package->name,
        'store_category_id' => null,
        'price' => 1000,
        'type' => 'minecraft_package',
        'is_pay_what_you_want' => false, 'is_gift_card_amount_same_as_price' => false,
        'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
        'is_featured' => false, 'is_giftable' => false,
        'required_packages_mode' => 'all', 'min_quantity' => 1,
        'prices' => [
            ['currency_code' => 'JPY', 'price' => 1200],
        ],
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_package_prices', [
        'store_package_id' => $package->id,
        'currency_code' => 'JPY',
        'price' => 1200,
    ]);
});

test('removing a price override reverts to the converted price', function () {
    $this->actingAs(User::whereId(1)->first());
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $package->prices()->create(['currency_code' => 'JPY', 'price' => 1200]);

    $this->put(route('admin.store.package.update', $package->id), [
        'name' => $package->name,
        'store_category_id' => null,
        'price' => 1000,
        'type' => 'minecraft_package',
        'is_pay_what_you_want' => false, 'is_gift_card_amount_same_as_price' => false,
        'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
        'is_featured' => false, 'is_giftable' => false,
        'required_packages_mode' => 'all', 'min_quantity' => 1,
        'prices' => [],
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('store_package_prices', ['store_package_id' => $package->id]);
});

test('a visitor can switch currency and it persists for a logged in user', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('home'))
        ->post(route('store.currency.switch'), ['code' => 'JPY'])
        ->assertRedirect(route('home'));

    expect(session('store_currency'))->toEqual('JPY');
    expect($user->fresh()->settings['store_currency'])->toEqual('JPY');
});

test('switching to a disabled currency is ignored rather than erroring', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create(['is_enabled' => false]);

    $this->from(route('home'))
        ->post(route('store.currency.switch'), ['code' => 'JPY'])
        ->assertRedirect(route('home'));

    expect(session('store_currency'))->toBeNull();
});

test('switching currency is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);
    $this->baseCurrency();

    $this->post(route('store.currency.switch'), ['code' => 'USD'])->assertStatus(404);
});

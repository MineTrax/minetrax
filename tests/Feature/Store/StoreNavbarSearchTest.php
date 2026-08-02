<?php

use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

/**
 * @return array<string, mixed>
 */
function navbarSearch(string $term): array
{
    return test()->getJson(route('search', ['q' => $term]))->assertOk()->json();
}

test('the navbar search returns matching packages under a shop heading', function () {
    // "shop" rather than the table name, so the dropdown has a heading a shopper recognises.
    StorePackage::factory()->create(['name' => 'Diamond Rank', 'price' => 1500]);

    $results = navbarSearch('diamond');

    expect($results)->toHaveKey('shop');
    expect($results['shop'])->toHaveCount(1);
    expect($results['shop'][0]['title'])->toBe('Diamond Rank');
});

test('a shop result carries what the dropdown row needs', function () {
    $package = StorePackage::factory()->create(['name' => 'Gold Rank', 'price' => 2500]);

    $row = navbarSearch('gold')['shop'][0];

    expect($row['slug'])->toBe($package->slug);
    expect($row['price_formatted'])->toBe('$25.00');
    expect($row['url'])->toBe(route('store.package', $package->slug));
    // The model itself must never reach the browser — it carries commands and cost fields.
    expect($row)->not->toHaveKey('searchable');
});

test('a shop result is priced through the pricing service, so a sale reaches the dropdown', function () {
    // Working the price out in the controller would show one figure in search and a lower one on
    // the package page — the exact drift the storefront was already fixed for.
    StorePackage::factory()->create(['name' => 'Diamond Rank', 'price' => 2000]);
    StoreSale::factory()->create(['discount_value' => 2500]);

    expect(navbarSearch('diamond')['shop'][0]['price_formatted'])->toBe('$15.00');
});

test('the short description is searchable too', function () {
    StorePackage::factory()->create(['name' => 'Starter Kit', 'short_description' => 'Includes a diamond pickaxe']);

    expect(navbarSearch('pickaxe')['shop'])->toHaveCount(1);
});

test('an unlisted package never surfaces in search', function () {
    // Hidden means unlisted on purpose — that is how a secret or promo package works. Search is a
    // listing, so putting one here would be a way to find every hidden package by guessing.
    StorePackage::factory()->hidden()->create(['name' => 'Secret Rank']);

    expect(navbarSearch('secret'))->not->toHaveKey('shop');
});

test('a disabled or out of window package never surfaces in search', function () {
    StorePackage::factory()->disabled()->create(['name' => 'Retired Rank']);
    StorePackage::factory()->create(['name' => 'Retired Bundle', 'available_until' => now()->subDay()]);
    StorePackage::factory()->create(['name' => 'Retired Crate', 'available_from' => now()->addWeek()]);

    expect(navbarSearch('retired'))->not->toHaveKey('shop');
});

test('the shop aspect is absent entirely when the store module is off', function () {
    config(['store.enabled' => false]);
    StorePackage::factory()->create(['name' => 'Diamond Rank']);

    expect(navbarSearch('diamond'))->not->toHaveKey('shop');
});

test('searching still works for people when the store has nothing to offer', function () {
    // The shop aspect must not break the two sections the search already had.
    $user = User::factory()->create(['username' => 'notch']);

    $results = navbarSearch('notch');

    expect($results)->toHaveKey('users');
    expect($results['users'][0]['username'])->toBe($user->username);
});

test('shop results are capped like every other aspect', function () {
    StorePackage::factory()->count(8)->create(['name' => 'Crate Key']);

    expect(navbarSearch('crate')['shop'])->toHaveCount(5);
});

test('a shop result is priced in the currency the visitor is shopping in', function () {
    StoreCurrency::factory()->create([
        'code' => 'EUR',
        'symbol' => '€',
        'rate_to_base' => 0.5,
        'is_enabled' => true,
        'is_base' => false,
    ]);
    StorePackage::factory()->create(['name' => 'Diamond Rank', 'price' => 2000]);

    $this->post(route('store.currency.switch'), ['code' => 'EUR']);

    expect(navbarSearch('diamond')['shop'][0]['price_formatted'])->toContain('€');
});

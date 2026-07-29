<?php

use App\Enums\StorePriceRounding;
use App\Models\Country;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StorePackagePrice;
use App\Models\User;
use App\Services\StoreCurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->service = app(StoreCurrencyService::class);
});

test('base currency falls back to settings when the table is empty', function () {
    $base = $this->service->base();

    expect($base->code)->toEqual('USD');
    expect($base->exponent)->toEqual(2);
    expect($base->is_base)->toBeTrue();
});

test('iso exponents come from brick money', function () {
    expect($this->service->exponentFor('USD'))->toEqual(2);
    expect($this->service->exponentFor('JPY'))->toEqual(0, 'JPY has no minor unit.');
    expect($this->service->exponentFor('KWD'))->toEqual(3, 'KWD has three minor-unit digits.');
    expect($this->service->exponentFor('EUR'))->toEqual(2);
});

test('minor units convert to decimal respecting the exponent', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    $dinar = StoreCurrency::factory()->threeDecimal()->create();

    expect($this->service->toDecimal(999, 'USD'))->toEqual('9.99');

    // The whole point: 1000 minor units of JPY is ¥1000, not ¥10.00.
    expect($this->service->toDecimal(1000, $yen))->toEqual('1000');
    expect($this->service->toDecimal(1234, $dinar))->toEqual('1.234');
});

test('decimal converts to minor units respecting the exponent', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    $dinar = StoreCurrency::factory()->threeDecimal()->create();

    expect($this->service->toMinor('9.99', 'USD'))->toBe(999);

    // A naive "* 100" would produce 100000 here and overcharge by 100x.
    expect($this->service->toMinor('1000', $yen))->toBe(1000);
    expect($this->service->toMinor('1.234', $dinar))->toBe(1234);
});

test('conversion from base uses the rate', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();

    // rate 150
    // $10.00 at 150 JPY per USD is ¥1500 — 1500 minor units, because JPY has no minor unit.
    expect($this->service->fromBase(1000, $yen))->toBe(1500);
});

test('conversion back to base round trips', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();

    expect($this->service->toBase($this->service->fromBase(1000, $yen), $yen))->toBe(1000);
});

test('converting a currency to itself is a no op', function () {
    $base = $this->baseCurrency();

    expect($this->service->convert(1999, $base, $base))->toBe(1999);
});

test('formatting respects symbol and position', function () {
    $this->baseCurrency();
    $euro = StoreCurrency::factory()->create([
        'code' => 'EUR', 'symbol' => '€', 'symbol_position' => 'suffix', 'exponent' => 2, 'rate_to_base' => 1,
    ]);
    $yen = StoreCurrency::factory()->zeroDecimal()->create();

    expect($this->service->format(999, 'USD'))->toEqual('$9.99');
    expect($this->service->format(999, $euro))->toEqual('9.99 €');
    expect($this->service->format(1500, $yen))->toEqual('¥1,500');
});

test('rounding rules apply to converted prices', function () {
    $whole = StoreCurrency::factory()->create([
        'code' => 'AAA', 'exponent' => 2, 'price_rounding' => StorePriceRounding::NEAREST_WHOLE,
    ]);
    $charm = StoreCurrency::factory()->create([
        'code' => 'BBB', 'exponent' => 2, 'price_rounding' => StorePriceRounding::CHARM_99,
    ]);
    $none = StoreCurrency::factory()->create([
        'code' => 'CCC', 'exponent' => 2, 'price_rounding' => StorePriceRounding::NONE,
    ]);

    expect($this->service->applyRounding(1234, $whole))->toBe(1200);
    expect($this->service->applyRounding(1234, $charm))->toBe(1299);
    expect($this->service->applyRounding(1234, $none))->toBe(1234);
});

test('charm rounding degrades gracefully for zero decimal currencies', function () {
    $yen = StoreCurrency::factory()->zeroDecimal()->create(['price_rounding' => StorePriceRounding::CHARM_99]);

    // There is no ".99" in a currency without a minor unit; the amount must stay whole.
    expect($this->service->applyRounding(1500, $yen))->toBe(1500);
});

test('package price in base currency is the stored price', function () {
    $base = $this->baseCurrency();
    $package = StorePackage::factory()->create(['price' => 999]);

    expect($this->service->priceForPackage($package, $base))->toBe(999);
});

test('package price converts when no override exists', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);

    // $10.00
    expect($this->service->priceForPackage($package, $yen))->toBe(1500);
});

test('an explicit override beats conversion and bypasses rounding', function () {
    $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create(['price_rounding' => StorePriceRounding::NEAREST_WHOLE]);
    $package = StorePackage::factory()->create(['price' => 1000]);

    StorePackagePrice::create([
        'store_package_id' => $package->id,
        'currency_code' => 'JPY',
        'price' => 1234,
    ]);

    expect($this->service->priceForPackage($package->fresh(), $yen))->toBe(1234);
});

test('resolution prefers the session switcher', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();

    session(['store_currency' => 'JPY']);

    expect(app(StoreCurrencyService::class)->resolve()->code)->toEqual('JPY');
});

test('resolution ignores a disabled currency', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create(['is_enabled' => false]);

    session(['store_currency' => 'JPY']);

    expect(app(StoreCurrencyService::class)->resolve()->code)->toEqual('USD');
});

test('resolution falls back to the user preference', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();

    $user = User::factory()->create(['settings' => ['store_currency' => 'JPY']]);
    $this->actingAs($user);

    expect(app(StoreCurrencyService::class)->resolve()->code)->toEqual('JPY');
});

test('resolution falls back to the country mapping', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create(['country_codes' => ['JP']]);

    $japan = Country::where('iso_code', 'JP')->first();
    $this->actingAs(User::factory()->create(['country_id' => $japan->id]));

    expect(app(StoreCurrencyService::class)->resolve()->code)->toEqual('JPY');
});

test('resolution falls back to base when nothing matches', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();

    expect(app(StoreCurrencyService::class)->resolve()->code)->toEqual('USD');
});

test('present returns both raw and formatted so vue never does money math', function () {
    $base = $this->baseCurrency();

    expect($this->service->present(999, $base))->toEqual([
        'amount' => 999,
        'currency' => 'USD',
        'formatted' => '$9.99',
        'exponent' => 2,
    ]);
});

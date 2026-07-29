<?php

use App\Models\StoreCurrency;
use App\Models\User;
use App\Settings\StoreSettings;
use Database\Seeders\StoreCurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);

    // The suite seeds the full DatabaseSeeder, so start from a clean table to exercise the
    // fresh-install path this seeder exists for.
    StoreCurrency::query()->delete();
});

function seedCurrencies(): void
{
    (new StoreCurrencySeeder)->run();
}

test('it creates the base currency from the store setting', function () {
    seedCurrencies();

    $base = StoreCurrency::where('is_base', true)->first();

    expect($base)->not->toBeNull();
    expect($base->code)->toEqual('USD');
    expect($base->name)->toEqual('US Dollar');
    expect((int) $base->exponent)->toEqual(2);
    expect((int) $base->rate_to_base)->toEqual(1);
    expect((bool) $base->is_enabled)->toBeTrue();
});

test('it reads the exponent from the iso table rather than assuming two', function () {
    $settings = app(StoreSettings::class);
    $settings->base_currency = 'JPY';
    $settings->save();

    seedCurrencies();

    $base = StoreCurrency::where('is_base', true)->first();

    expect($base->code)->toEqual('JPY');
    expect((int) $base->exponent)->toEqual(0, 'Yen has no minor unit.');
});

test('a three decimal base currency is seeded correctly', function () {
    $settings = app(StoreSettings::class);
    $settings->base_currency = 'KWD';
    $settings->save();

    seedCurrencies();

    expect((int) StoreCurrency::where('is_base', true)->first()->exponent)->toEqual(3);
});

test('running it twice creates only one currency', function () {
    seedCurrencies();
    seedCurrencies();

    expect(StoreCurrency::count())->toEqual(1);
});

test('it never demotes a base currency the admin already chose', function () {
    StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => true, 'rate_to_base' => 1]);

    seedCurrencies();

    expect(StoreCurrency::where('is_base', true)->first()->code)->toEqual('EUR');
    expect(StoreCurrency::count())->toEqual(1, 'It must not add a second currency alongside an existing base.');
});

test('it promotes a matching row rather than duplicating it', function () {
    // `code` is unique, so a row the admin created by hand has to be promoted, not re-inserted.
    StoreCurrency::factory()->create(['code' => 'USD', 'is_base' => false, 'is_enabled' => false]);

    seedCurrencies();

    expect(StoreCurrency::count())->toEqual(1);

    $usd = StoreCurrency::first();
    expect((bool) $usd->is_base)->toBeTrue();
    expect((bool) $usd->is_enabled)->toBeTrue();
});

test('the settings page can offer the seeded currency', function () {
    seedCurrencies();

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.setting.store.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('currencies', 1));
});

test('the first currency created by hand becomes the base', function () {
    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.currency.store'), [
            'code' => 'GBP',
            'name' => 'Pound Sterling',
            'symbol' => '£',
            'symbol_position' => 'prefix',
            'exponent' => 2,
            'rate_to_base' => 0.8,
            'is_enabled' => false,
            'price_rounding' => 'none',
            'sort_order' => 0,
        ])
        ->assertRedirect();

    $currency = StoreCurrency::where('code', 'GBP')->first();

    expect((bool) $currency->is_base)->toBeTrue('A store cannot be left with no base currency.');
    expect((int) $currency->rate_to_base)->toEqual(1, 'The base currency is its own unit.');
    expect((bool) $currency->is_enabled)->toBeTrue('The base currency has to be usable.');
});

test('a second currency created by hand does not steal the base', function () {
    seedCurrencies();

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.currency.store'), [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'symbol_position' => 'suffix',
            'exponent' => 2,
            'rate_to_base' => 0.92,
            'is_enabled' => true,
            'price_rounding' => 'none',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect((bool) StoreCurrency::where('code', 'EUR')->first()->is_base)->toBeFalse();
    expect(StoreCurrency::where('is_base', true)->first()->code)->toEqual('USD');
    expect(StoreCurrency::where('is_base', true)->count())->toEqual(1, 'Exactly one base, always.');
});

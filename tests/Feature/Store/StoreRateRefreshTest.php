<?php

use App\Contracts\StoreExchangeRateProviderContract;
use App\Jobs\Store\RefreshStoreCurrencyRatesJob;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Services\StoreCurrencyService;
use App\Settings\StoreSettings;
use App\Utils\ExchangeRates\FrankfurterExchangeRateProvider;
use App\Utils\ExchangeRates\StoreExchangeRateProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function setRateSource(string $source): void
{
    $settings = app(StoreSettings::class);
    $settings->currency_rate_source = $source;
    $settings->save();
}

function rateRefreshRunJob(): void
{
    (new RefreshStoreCurrencyRatesJob)->handle(
        app(StoreSettings::class),
        app(StoreExchangeRateProviderManager::class)
    );
}

function fakeFrankfurter(array $rates): void
{
    Http::fake([
        'api.frankfurter.app/*' => Http::response([
            'base' => 'USD',
            'date' => now()->toDateString(),
            'rates' => $rates,
        ]),
    ]);
}

test('rates are written for every enabled currency', function () {
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5]);
    $yen = StoreCurrency::factory()->zeroDecimal()->create(['rate_to_base' => 100]);

    fakeFrankfurter(['EUR' => 0.9241, 'JPY' => 151.2]);

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.9241);
    expect((float) $yen->fresh()->rate_to_base)->toEqual(151.2);
    expect($euro->fresh()->rate_updated_at)->not->toBeNull();
});

test('manual rate source is left completely alone', function () {
    // The admin typed these in. An automatic refresh would silently overwrite them.
    setRateSource('manual');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5]);

    Http::fake();

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.5);
    Http::assertNothingSent();
});

test('the base currency is never asked for or rewritten', function () {
    setRateSource('api');
    $base = $this->baseCurrency();
    StoreCurrency::factory()->create(['code' => 'EUR']);

    fakeFrankfurter(['EUR' => 0.9241]);

    rateRefreshRunJob();

    expect((float) $base->fresh()->rate_to_base)->toEqual(1);
    Http::assertSent(fn ($request) => ! str_contains($request->url(), $base->code.',')
        && str_contains($request->url(), 'base='.$base->code));
});

test('a disabled currency is not refreshed', function () {
    setRateSource('api');
    $disabled = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5, 'is_enabled' => false]);

    Http::fake();

    rateRefreshRunJob();

    expect((float) $disabled->fresh()->rate_to_base)->toEqual(0.5);
    Http::assertNothingSent();
});

test('a failed feed keeps the last known rates', function () {
    // A currency whose rate went to zero would price the entire catalogue at nothing, which is
    // far worse than a rate being a day stale.
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);

    Http::fake(['api.frankfurter.app/*' => Http::response('gateway down', 503)]);

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.9);
});

test('a zero or negative rate is refused', function () {
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);
    $yen = StoreCurrency::factory()->zeroDecimal()->create(['rate_to_base' => 150]);

    fakeFrankfurter(['EUR' => 0, 'JPY' => -3]);

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.9);
    expect((float) $yen->fresh()->rate_to_base)->toEqual(150);
});

test('a currency the feed does not carry keeps its manual rate', function () {
    // Frankfurter carries the ~30 currencies the ECB publishes. Anything else stays the admin's
    // to maintain, rather than being zeroed for being absent.
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);
    $exotic = StoreCurrency::factory()->create(['code' => 'KWD', 'exponent' => 3, 'rate_to_base' => 0.31]);

    fakeFrankfurter(['EUR' => 0.9241]);

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.9241);
    expect((float) $exotic->fresh()->rate_to_base)->toEqual(0.31);
});

test('a new rate reprices the storefront but not an existing order', function () {
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 1]);
    $package = StorePackage::factory()->create(['price' => 1000]);

    $order = StoreOrder::factory()->completed()->create([
        'currency' => 'EUR',
        'total' => 1000,
        'base_total' => 1000,
        'exchange_rate' => 1,
    ]);

    fakeFrankfurter(['EUR' => 2]);
    rateRefreshRunJob();

    $currencies = app(StoreCurrencyService::class);

    // Tomorrow's price follows the new rate.
    expect($currencies->priceForPackage($package->fresh(), $euro->fresh()))->toEqual(2000);

    // What was charged does not.
    $order->refresh();
    expect((int) $order->total)->toEqual(1000);
    expect((int) $order->base_total)->toEqual(1000);
    expect((float) $order->exchange_rate)->toEqual(1);
});

test('an explicit price override ignores the rate entirely', function () {
    setRateSource('api');
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 1]);
    $package = StorePackage::factory()->create(['price' => 1000]);
    $package->prices()->create(['currency_code' => 'EUR', 'price' => 899]);

    fakeFrankfurter(['EUR' => 5]);
    rateRefreshRunJob();

    expect(app(StoreCurrencyService::class)->priceForPackage($package->fresh(), $euro->fresh()))->toEqual(899);
});

test('an unknown provider key is a skipped run rather than an error', function () {
    setRateSource('api');
    config(['store.rate_provider' => 'not-a-provider']);
    $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);

    Http::fake();

    rateRefreshRunJob();

    expect((float) $euro->fresh()->rate_to_base)->toEqual(0.9);
    Http::assertNothingSent();
});

test('the provider registry resolves the configured provider', function () {
    $manager = app(StoreExchangeRateProviderManager::class);

    expect($manager->active())->toBeInstanceOf(FrankfurterExchangeRateProvider::class);
    expect($manager->providerOrFail('frankfurter'))->toBeInstanceOf(StoreExchangeRateProviderContract::class);
});

test('every registered provider satisfies the contract', function () {
    // Mirrors StoreGatewayContractTest: a provider added later is covered the moment it is
    // registered, without anyone remembering to write a test for it.
    $providers = app(StoreExchangeRateProviderManager::class)->all();

    expect($providers)->not->toBeEmpty();

    foreach ($providers as $key => $provider) {
        expect($provider)->toBeInstanceOf(StoreExchangeRateProviderContract::class);
        expect($provider->key())->toBe($key, 'The registry key must match the provider key.');
        expect($provider->label())->not->toBeEmpty();
    }
});

test('the provider returns rates as strings so precision survives', function () {
    // rate_to_base is decimal(20,10); a float would round a long rate on the way past.
    fakeFrankfurter(['EUR' => 0.9241377209]);

    $rates = app(FrankfurterExchangeRateProvider::class)->ratesFor('USD', ['EUR']);

    expect($rates)->toBe(['EUR' => '0.9241377209']);
});

test('the provider throws when the feed answers without rates', function () {
    Http::fake(['api.frankfurter.app/*' => Http::response(['base' => 'USD'])]);

    $this->expectException(RuntimeException::class);

    app(FrankfurterExchangeRateProvider::class)->ratesFor('USD', ['EUR']);
});

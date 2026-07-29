<?php

namespace Tests\Feature\Store;

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
use Tests\TestCase;

/**
 * The daily rate refresh. Its two hard rules: never write a rate that would give the catalogue
 * away, and never touch what an existing order says it charged.
 */
class StoreRateRefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();
    }

    private function setRateSource(string $source): void
    {
        $settings = app(StoreSettings::class);
        $settings->currency_rate_source = $source;
        $settings->save();
    }

    private function runJob(): void
    {
        (new RefreshStoreCurrencyRatesJob)->handle(
            app(StoreSettings::class),
            app(StoreExchangeRateProviderManager::class)
        );
    }

    private function fakeFrankfurter(array $rates): void
    {
        Http::fake([
            'api.frankfurter.app/*' => Http::response([
                'base' => 'USD',
                'date' => now()->toDateString(),
                'rates' => $rates,
            ]),
        ]);
    }

    public function test_rates_are_written_for_every_enabled_currency()
    {
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5]);
        $yen = StoreCurrency::factory()->zeroDecimal()->create(['rate_to_base' => 100]);

        $this->fakeFrankfurter(['EUR' => 0.9241, 'JPY' => 151.2]);

        $this->runJob();

        $this->assertEquals(0.9241, (float) $euro->fresh()->rate_to_base);
        $this->assertEquals(151.2, (float) $yen->fresh()->rate_to_base);
        $this->assertNotNull($euro->fresh()->rate_updated_at);
    }

    public function test_manual_rate_source_is_left_completely_alone()
    {
        // The admin typed these in. An automatic refresh would silently overwrite them.
        $this->setRateSource('manual');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5]);

        Http::fake();

        $this->runJob();

        $this->assertEquals(0.5, (float) $euro->fresh()->rate_to_base);
        Http::assertNothingSent();
    }

    public function test_the_base_currency_is_never_asked_for_or_rewritten()
    {
        $this->setRateSource('api');
        $base = $this->baseCurrency();
        StoreCurrency::factory()->create(['code' => 'EUR']);

        $this->fakeFrankfurter(['EUR' => 0.9241]);

        $this->runJob();

        $this->assertEquals(1, (float) $base->fresh()->rate_to_base);
        Http::assertSent(fn ($request) => ! str_contains($request->url(), $base->code.',')
            && str_contains($request->url(), 'base='.$base->code));
    }

    public function test_a_disabled_currency_is_not_refreshed()
    {
        $this->setRateSource('api');
        $disabled = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.5, 'is_enabled' => false]);

        Http::fake();

        $this->runJob();

        $this->assertEquals(0.5, (float) $disabled->fresh()->rate_to_base);
        Http::assertNothingSent();
    }

    public function test_a_failed_feed_keeps_the_last_known_rates()
    {
        // A currency whose rate went to zero would price the entire catalogue at nothing, which is
        // far worse than a rate being a day stale.
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);

        Http::fake(['api.frankfurter.app/*' => Http::response('gateway down', 503)]);

        $this->runJob();

        $this->assertEquals(0.9, (float) $euro->fresh()->rate_to_base);
    }

    public function test_a_zero_or_negative_rate_is_refused()
    {
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);
        $yen = StoreCurrency::factory()->zeroDecimal()->create(['rate_to_base' => 150]);

        $this->fakeFrankfurter(['EUR' => 0, 'JPY' => -3]);

        $this->runJob();

        $this->assertEquals(0.9, (float) $euro->fresh()->rate_to_base);
        $this->assertEquals(150, (float) $yen->fresh()->rate_to_base);
    }

    public function test_a_currency_the_feed_does_not_carry_keeps_its_manual_rate()
    {
        // Frankfurter carries the ~30 currencies the ECB publishes. Anything else stays the admin's
        // to maintain, rather than being zeroed for being absent.
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);
        $exotic = StoreCurrency::factory()->create(['code' => 'KWD', 'exponent' => 3, 'rate_to_base' => 0.31]);

        $this->fakeFrankfurter(['EUR' => 0.9241]);

        $this->runJob();

        $this->assertEquals(0.9241, (float) $euro->fresh()->rate_to_base);
        $this->assertEquals(0.31, (float) $exotic->fresh()->rate_to_base);
    }

    public function test_a_new_rate_reprices_the_storefront_but_not_an_existing_order()
    {
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 1]);
        $package = StorePackage::factory()->create(['price' => 1000]);

        $order = StoreOrder::factory()->completed()->create([
            'currency' => 'EUR',
            'total' => 1000,
            'base_total' => 1000,
            'exchange_rate' => 1,
        ]);

        $this->fakeFrankfurter(['EUR' => 2]);
        $this->runJob();

        $currencies = app(StoreCurrencyService::class);

        // Tomorrow's price follows the new rate.
        $this->assertEquals(2000, $currencies->priceForPackage($package->fresh(), $euro->fresh()));

        // What was charged does not.
        $order->refresh();
        $this->assertEquals(1000, (int) $order->total);
        $this->assertEquals(1000, (int) $order->base_total);
        $this->assertEquals(1, (float) $order->exchange_rate);
    }

    public function test_an_explicit_price_override_ignores_the_rate_entirely()
    {
        $this->setRateSource('api');
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 1]);
        $package = StorePackage::factory()->create(['price' => 1000]);
        $package->prices()->create(['currency_code' => 'EUR', 'price' => 899]);

        $this->fakeFrankfurter(['EUR' => 5]);
        $this->runJob();

        $this->assertEquals(899, app(StoreCurrencyService::class)->priceForPackage($package->fresh(), $euro->fresh()));
    }

    public function test_an_unknown_provider_key_is_a_skipped_run_rather_than_an_error()
    {
        $this->setRateSource('api');
        config(['store.rate_provider' => 'not-a-provider']);
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.9]);

        Http::fake();

        $this->runJob();

        $this->assertEquals(0.9, (float) $euro->fresh()->rate_to_base);
        Http::assertNothingSent();
    }

    public function test_the_provider_registry_resolves_the_configured_provider()
    {
        $manager = app(StoreExchangeRateProviderManager::class);

        $this->assertInstanceOf(FrankfurterExchangeRateProvider::class, $manager->active());
        $this->assertInstanceOf(StoreExchangeRateProviderContract::class, $manager->providerOrFail('frankfurter'));
    }

    public function test_every_registered_provider_satisfies_the_contract()
    {
        // Mirrors StoreGatewayContractTest: a provider added later is covered the moment it is
        // registered, without anyone remembering to write a test for it.
        $providers = app(StoreExchangeRateProviderManager::class)->all();

        $this->assertNotEmpty($providers);

        foreach ($providers as $key => $provider) {
            $this->assertInstanceOf(StoreExchangeRateProviderContract::class, $provider);
            $this->assertSame($key, $provider->key(), 'The registry key must match the provider key.');
            $this->assertNotEmpty($provider->label());
        }
    }

    public function test_the_provider_returns_rates_as_strings_so_precision_survives()
    {
        // rate_to_base is decimal(20,10); a float would round a long rate on the way past.
        $this->fakeFrankfurter(['EUR' => 0.9241377209]);

        $rates = app(FrankfurterExchangeRateProvider::class)->ratesFor('USD', ['EUR']);

        $this->assertSame(['EUR' => '0.9241377209'], $rates);
    }

    public function test_the_provider_throws_when_the_feed_answers_without_rates()
    {
        Http::fake(['api.frankfurter.app/*' => Http::response(['base' => 'USD'])]);

        $this->expectException(\RuntimeException::class);

        app(FrankfurterExchangeRateProvider::class)->ratesFor('USD', ['EUR']);
    }
}

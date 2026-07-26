<?php

namespace Tests\Feature\Store;

use App\Enums\StorePriceRounding;
use App\Models\Country;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StorePackagePrice;
use App\Models\User;
use App\Services\StoreCurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCurrencyTest extends TestCase
{
    use RefreshDatabase;

    private StoreCurrencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->service = app(StoreCurrencyService::class);
    }

    public function test_base_currency_falls_back_to_settings_when_the_table_is_empty()
    {
        $base = $this->service->base();

        $this->assertEquals('USD', $base->code);
        $this->assertEquals(2, $base->exponent);
        $this->assertTrue($base->is_base);
    }

    public function test_iso_exponents_come_from_brick_money()
    {
        $this->assertEquals(2, $this->service->exponentFor('USD'));
        $this->assertEquals(0, $this->service->exponentFor('JPY'), 'JPY has no minor unit.');
        $this->assertEquals(3, $this->service->exponentFor('KWD'), 'KWD has three minor-unit digits.');
        $this->assertEquals(2, $this->service->exponentFor('EUR'));
    }

    public function test_minor_units_convert_to_decimal_respecting_the_exponent()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create();
        $dinar = StoreCurrency::factory()->threeDecimal()->create();

        $this->assertEquals('9.99', $this->service->toDecimal(999, 'USD'));
        // The whole point: 1000 minor units of JPY is ¥1000, not ¥10.00.
        $this->assertEquals('1000', $this->service->toDecimal(1000, $yen));
        $this->assertEquals('1.234', $this->service->toDecimal(1234, $dinar));
    }

    public function test_decimal_converts_to_minor_units_respecting_the_exponent()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create();
        $dinar = StoreCurrency::factory()->threeDecimal()->create();

        $this->assertSame(999, $this->service->toMinor('9.99', 'USD'));
        // A naive "* 100" would produce 100000 here and overcharge by 100x.
        $this->assertSame(1000, $this->service->toMinor('1000', $yen));
        $this->assertSame(1234, $this->service->toMinor('1.234', $dinar));
    }

    public function test_conversion_from_base_uses_the_rate()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create(); // rate 150

        // $10.00 at 150 JPY per USD is ¥1500 — 1500 minor units, because JPY has no minor unit.
        $this->assertSame(1500, $this->service->fromBase(1000, $yen));
    }

    public function test_conversion_back_to_base_round_trips()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create();

        $this->assertSame(1000, $this->service->toBase($this->service->fromBase(1000, $yen), $yen));
    }

    public function test_converting_a_currency_to_itself_is_a_no_op()
    {
        $base = $this->baseCurrency();

        $this->assertSame(1999, $this->service->convert(1999, $base, $base));
    }

    public function test_formatting_respects_symbol_and_position()
    {
        $this->baseCurrency();
        $euro = StoreCurrency::factory()->create([
            'code' => 'EUR', 'symbol' => '€', 'symbol_position' => 'suffix', 'exponent' => 2, 'rate_to_base' => 1,
        ]);
        $yen = StoreCurrency::factory()->zeroDecimal()->create();

        $this->assertEquals('$9.99', $this->service->format(999, 'USD'));
        $this->assertEquals('9.99 €', $this->service->format(999, $euro));
        $this->assertEquals('¥1,500', $this->service->format(1500, $yen));
    }

    public function test_rounding_rules_apply_to_converted_prices()
    {
        $whole = StoreCurrency::factory()->create([
            'code' => 'AAA', 'exponent' => 2, 'price_rounding' => StorePriceRounding::NEAREST_WHOLE,
        ]);
        $charm = StoreCurrency::factory()->create([
            'code' => 'BBB', 'exponent' => 2, 'price_rounding' => StorePriceRounding::CHARM_99,
        ]);
        $none = StoreCurrency::factory()->create([
            'code' => 'CCC', 'exponent' => 2, 'price_rounding' => StorePriceRounding::NONE,
        ]);

        $this->assertSame(1200, $this->service->applyRounding(1234, $whole));
        $this->assertSame(1299, $this->service->applyRounding(1234, $charm));
        $this->assertSame(1234, $this->service->applyRounding(1234, $none));
    }

    public function test_charm_rounding_degrades_gracefully_for_zero_decimal_currencies()
    {
        $yen = StoreCurrency::factory()->zeroDecimal()->create(['price_rounding' => StorePriceRounding::CHARM_99]);

        // There is no ".99" in a currency without a minor unit; the amount must stay whole.
        $this->assertSame(1500, $this->service->applyRounding(1500, $yen));
    }

    public function test_package_price_in_base_currency_is_the_stored_price()
    {
        $base = $this->baseCurrency();
        $package = StorePackage::factory()->create(['price' => 999]);

        $this->assertSame(999, $this->service->priceForPackage($package, $base));
    }

    public function test_package_price_converts_when_no_override_exists()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create();
        $package = StorePackage::factory()->create(['price' => 1000]); // $10.00

        $this->assertSame(1500, $this->service->priceForPackage($package, $yen));
    }

    public function test_an_explicit_override_beats_conversion_and_bypasses_rounding()
    {
        $this->baseCurrency();
        $yen = StoreCurrency::factory()->zeroDecimal()->create(['price_rounding' => StorePriceRounding::NEAREST_WHOLE]);
        $package = StorePackage::factory()->create(['price' => 1000]);

        StorePackagePrice::create([
            'store_package_id' => $package->id,
            'currency_code' => 'JPY',
            'price' => 1234,
        ]);

        $this->assertSame(1234, $this->service->priceForPackage($package->fresh(), $yen));
    }

    public function test_resolution_prefers_the_session_switcher()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();

        session(['store_currency' => 'JPY']);

        $this->assertEquals('JPY', app(StoreCurrencyService::class)->resolve()->code);
    }

    public function test_resolution_ignores_a_disabled_currency()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create(['is_enabled' => false]);

        session(['store_currency' => 'JPY']);

        $this->assertEquals('USD', app(StoreCurrencyService::class)->resolve()->code);
    }

    public function test_resolution_falls_back_to_the_user_preference()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();

        $user = User::factory()->create(['settings' => ['store_currency' => 'JPY']]);
        $this->actingAs($user);

        $this->assertEquals('JPY', app(StoreCurrencyService::class)->resolve()->code);
    }

    public function test_resolution_falls_back_to_the_country_mapping()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create(['country_codes' => ['JP']]);

        $japan = Country::where('iso_code', 'JP')->first();
        $this->actingAs(User::factory()->create(['country_id' => $japan->id]));

        $this->assertEquals('JPY', app(StoreCurrencyService::class)->resolve()->code);
    }

    public function test_resolution_falls_back_to_base_when_nothing_matches()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();

        $this->assertEquals('USD', app(StoreCurrencyService::class)->resolve()->code);
    }

    public function test_present_returns_both_raw_and_formatted_so_vue_never_does_money_math()
    {
        $base = $this->baseCurrency();

        $this->assertEquals([
            'amount' => 999,
            'currency' => 'USD',
            'formatted' => '$9.99',
            'exponent' => 2,
        ], $this->service->present(999, $base));
    }
}

<?php

namespace Tests\Feature\Store;

use App\Models\StoreCurrency;
use App\Models\User;
use App\Settings\StoreSettings;
use Database\Seeders\StoreCurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exactly one currency must be the base. Zero is not a valid state: with an empty table the
 * base-currency picker in Store Settings has nothing to offer and the storefront runs on a
 * transient record that cannot be edited.
 */
class StoreCurrencySeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);

        // The suite seeds the full DatabaseSeeder, so start from a clean table to exercise the
        // fresh-install path this seeder exists for.
        StoreCurrency::query()->delete();
    }

    private function seedCurrencies(): void
    {
        (new StoreCurrencySeeder)->run();
    }

    public function test_it_creates_the_base_currency_from_the_store_setting()
    {
        $this->seedCurrencies();

        $base = StoreCurrency::where('is_base', true)->first();

        $this->assertNotNull($base);
        $this->assertEquals('USD', $base->code);
        $this->assertEquals('US Dollar', $base->name);
        $this->assertEquals(2, (int) $base->exponent);
        $this->assertEquals(1, (int) $base->rate_to_base);
        $this->assertTrue((bool) $base->is_enabled);
    }

    public function test_it_reads_the_exponent_from_the_iso_table_rather_than_assuming_two()
    {
        $settings = app(StoreSettings::class);
        $settings->base_currency = 'JPY';
        $settings->save();

        $this->seedCurrencies();

        $base = StoreCurrency::where('is_base', true)->first();

        $this->assertEquals('JPY', $base->code);
        $this->assertEquals(0, (int) $base->exponent, 'Yen has no minor unit.');
    }

    public function test_a_three_decimal_base_currency_is_seeded_correctly()
    {
        $settings = app(StoreSettings::class);
        $settings->base_currency = 'KWD';
        $settings->save();

        $this->seedCurrencies();

        $this->assertEquals(3, (int) StoreCurrency::where('is_base', true)->first()->exponent);
    }

    public function test_running_it_twice_creates_only_one_currency()
    {
        $this->seedCurrencies();
        $this->seedCurrencies();

        $this->assertEquals(1, StoreCurrency::count());
    }

    public function test_it_never_demotes_a_base_currency_the_admin_already_chose()
    {
        StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => true, 'rate_to_base' => 1]);

        $this->seedCurrencies();

        $this->assertEquals('EUR', StoreCurrency::where('is_base', true)->first()->code);
        $this->assertEquals(1, StoreCurrency::count(), 'It must not add a second currency alongside an existing base.');
    }

    public function test_it_promotes_a_matching_row_rather_than_duplicating_it()
    {
        // `code` is unique, so a row the admin created by hand has to be promoted, not re-inserted.
        StoreCurrency::factory()->create(['code' => 'USD', 'is_base' => false, 'is_enabled' => false]);

        $this->seedCurrencies();

        $this->assertEquals(1, StoreCurrency::count());

        $usd = StoreCurrency::first();
        $this->assertTrue((bool) $usd->is_base);
        $this->assertTrue((bool) $usd->is_enabled);
    }

    public function test_the_settings_page_can_offer_the_seeded_currency()
    {
        $this->seedCurrencies();

        $this->actingAs(User::whereId(1)->first())
            ->get(route('admin.setting.store.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('currencies', 1));
    }

    // --- The admin path to the same invariant ----------------------------------------------------

    public function test_the_first_currency_created_by_hand_becomes_the_base()
    {
        $this->actingAs(User::whereId(1)->first())
            ->post(route('admin.store-currency.store'), [
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

        $this->assertTrue((bool) $currency->is_base, 'A store cannot be left with no base currency.');
        $this->assertEquals(1, (int) $currency->rate_to_base, 'The base currency is its own unit.');
        $this->assertTrue((bool) $currency->is_enabled, 'The base currency has to be usable.');
    }

    public function test_a_second_currency_created_by_hand_does_not_steal_the_base()
    {
        $this->seedCurrencies();

        $this->actingAs(User::whereId(1)->first())
            ->post(route('admin.store-currency.store'), [
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

        $this->assertFalse((bool) StoreCurrency::where('code', 'EUR')->first()->is_base);
        $this->assertEquals('USD', StoreCurrency::where('is_base', true)->first()->code);
        $this->assertEquals(1, StoreCurrency::where('is_base', true)->count(), 'Exactly one base, always.');
    }
}

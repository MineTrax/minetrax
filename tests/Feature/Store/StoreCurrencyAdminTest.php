<?php

namespace Tests\Feature\Store;

use App\Enums\StorePriceRounding;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCurrencyAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
    }

    private function validPayload(array $overrides = []): array
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

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.currency.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.currency.index'))->assertStatus(302);
    }

    public function test_admin_can_create_a_currency()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.currency.store'), $this->validPayload())
            ->assertRedirect(route('admin.store.currency.index'));

        $this->assertDatabaseHas('store_currencies', ['code' => 'EUR', 'exponent' => 2, 'is_base' => false]);
        $this->assertEquals(['DE', 'FR'], StoreCurrency::where('code', 'EUR')->first()->country_codes);
    }

    public function test_currency_code_is_uppercased_and_must_be_three_letters()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.currency.store'), $this->validPayload(['code' => 'eur']));
        $this->assertDatabaseHas('store_currencies', ['code' => 'EUR']);

        $this->post(route('admin.store.currency.store'), $this->validPayload(['code' => 'EURO']))
            ->assertSessionHasErrors(['code']);
    }

    public function test_duplicate_currency_code_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCurrency::factory()->create(['code' => 'EUR']);

        $this->post(route('admin.store.currency.store'), $this->validPayload())
            ->assertSessionHasErrors(['code']);
    }

    public function test_exponent_is_constrained_to_the_range_iso_uses()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.currency.store'), $this->validPayload(['exponent' => 9]))
            ->assertSessionHasErrors(['exponent']);
    }

    public function test_rate_must_be_positive()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.currency.store'), $this->validPayload(['rate_to_base' => 0]))
            ->assertSessionHasErrors(['rate_to_base']);
    }

    public function test_the_base_currency_cannot_be_disabled_or_re_rated()
    {
        $this->actingAs(User::whereId(1)->first());
        $base = $this->baseCurrency();

        $this->put(route('admin.store.currency.update', $base->id), $this->validPayload([
            'code' => $base->code,
            'is_enabled' => false,
        ]))->assertSessionHasErrors(['is_enabled']);

        $this->put(route('admin.store.currency.update', $base->id), $this->validPayload([
            'code' => $base->code,
            'rate_to_base' => 2,
        ]))->assertSessionHasErrors(['rate_to_base']);
    }

    public function test_the_base_currency_cannot_be_deleted()
    {
        $this->actingAs(User::whereId(1)->first());
        $base = $this->baseCurrency();

        $this->delete(route('admin.store.currency.delete', $base->id));

        $this->assertDatabaseHas('store_currencies', ['id' => $base->id]);
    }

    public function test_a_currency_with_orders_cannot_be_deleted()
    {
        $this->actingAs(User::whereId(1)->first());
        $currency = StoreCurrency::factory()->create(['code' => 'EUR']);
        StoreOrder::factory()->create(['currency' => 'EUR']);

        $this->delete(route('admin.store.currency.delete', $currency->id));

        $this->assertDatabaseHas('store_currencies', ['id' => $currency->id]);
    }

    public function test_an_unused_currency_can_be_deleted()
    {
        $this->actingAs(User::whereId(1)->first());
        $currency = StoreCurrency::factory()->create(['code' => 'EUR']);

        $this->delete(route('admin.store.currency.delete', $currency->id));

        $this->assertDatabaseMissing('store_currencies', ['id' => $currency->id]);
    }

    public function test_base_currency_can_be_changed_while_no_orders_exist()
    {
        $this->actingAs(User::whereId(1)->first());
        $base = $this->baseCurrency();
        $euro = StoreCurrency::factory()->create(['code' => 'EUR', 'rate_to_base' => 0.92]);

        $this->post(route('admin.store.currency.make-base', $euro->id));

        $this->assertTrue($euro->fresh()->is_base);
        $this->assertFalse($base->fresh()->is_base);
        $this->assertEquals(1, (float) $euro->fresh()->rate_to_base);
    }

    public function test_base_currency_is_locked_once_an_order_exists()
    {
        // Historical base_total values were computed against the current base, so moving it
        // would silently rewrite past revenue.
        $this->actingAs(User::whereId(1)->first());
        $base = $this->baseCurrency();
        $euro = StoreCurrency::factory()->create(['code' => 'EUR']);
        StoreOrder::factory()->create();

        $this->post(route('admin.store.currency.make-base', $euro->id));

        $this->assertFalse($euro->fresh()->is_base);
        $this->assertTrue($base->fresh()->is_base);
    }

    public function test_admin_can_set_per_package_price_overrides()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();
        $package = StorePackage::factory()->create(['price' => 1000]);

        $this->put(route('admin.store.package.update', $package->id), [
            'name' => $package->name,
            'store_category_id' => null,
            'price' => 1000,
            'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
            'is_run_on_all_servers' => false, 'min_quantity' => 1,
            'prices' => [
                ['currency_code' => 'JPY', 'price' => 1200],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_package_prices', [
            'store_package_id' => $package->id,
            'currency_code' => 'JPY',
            'price' => 1200,
        ]);
    }

    public function test_removing_a_price_override_reverts_to_the_converted_price()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();
        $package = StorePackage::factory()->create(['price' => 1000]);
        $package->prices()->create(['currency_code' => 'JPY', 'price' => 1200]);

        $this->put(route('admin.store.package.update', $package->id), [
            'name' => $package->name,
            'store_category_id' => null,
            'price' => 1000,
            'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
            'is_run_on_all_servers' => false, 'min_quantity' => 1,
            'prices' => [],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('store_package_prices', ['store_package_id' => $package->id]);
    }

    public function test_a_visitor_can_switch_currency_and_it_persists_for_a_logged_in_user()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('store.currency.switch'), ['code' => 'JPY'])
            ->assertRedirect(route('home'));

        $this->assertEquals('JPY', session('store_currency'));
        $this->assertEquals('JPY', $user->fresh()->settings['store_currency']);
    }

    public function test_switching_to_a_disabled_currency_is_ignored_rather_than_erroring()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create(['is_enabled' => false]);

        $this->from(route('home'))
            ->post(route('store.currency.switch'), ['code' => 'JPY'])
            ->assertRedirect(route('home'));

        $this->assertNull(session('store_currency'));
    }

    public function test_switching_currency_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);
        $this->baseCurrency();

        $this->post(route('store.currency.switch'), ['code' => 'USD'])->assertStatus(404);
    }
}

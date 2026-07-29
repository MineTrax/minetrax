<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Services\StorePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSaleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();
    }

    private function validPayload(array $overrides = []): array
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

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.sale.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.sale.index'))->assertStatus(302);
    }

    public function test_staff_without_the_permission_are_forbidden()
    {
        // Moderator is staff but is granted no store permissions by RoleSeeder.
        $staff = User::factory()->create();
        $staff->assignRole('moderator');

        $this->actingAs($staff)->get(route('admin.store.sale.index'))->assertStatus(403);
    }

    public function test_the_index_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)->get(route('admin.store.sale.index'))->assertStatus(403);
    }

    public function test_superadmin_can_list_sales()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreSale::factory()->create(['name' => 'Listed']);

        $this->get(route('admin.store.sale.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreSale/IndexStoreSale')
                ->has('sales.data', 1)
            );
    }

    public function test_the_listing_reports_whether_a_sale_is_running_now()
    {
        // Enabled is not the same as running: a sale can be switched on but out of its window, and
        // the dates alone do not say which.
        $this->actingAs(User::whereId(1)->first());
        StoreSale::factory()->create(['name' => 'Live']);
        StoreSale::factory()->upcoming()->create(['name' => 'Later']);
        StoreSale::factory()->create(['name' => 'Off', 'is_enabled' => false]);

        $this->get(route('admin.store.sale.index'))
            ->assertInertia(function ($page) {
                $byName = collect($page->toArray()['props']['sales']['data'])->keyBy('name');

                $this->assertTrue($byName['Live']['is_running']);
                $this->assertFalse($byName['Later']['is_running']);
                $this->assertFalse($byName['Off']['is_running']);
            });
    }

    public function test_admin_can_create_a_percentage_sale()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.sale.store'), $this->validPayload())
            ->assertRedirect(route('admin.store.sale.index'));

        $this->assertDatabaseHas('store_sales', [
            'name' => 'Summer Sale',
            'discount_type' => 'percent',
            'discount_value' => 2500,
            'is_enabled' => true,
        ]);
    }

    public function test_a_percentage_above_one_hundred_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.sale.store'), $this->validPayload(['discount_value' => 10001]))
            ->assertSessionHasErrors(['discount_value']);
    }

    public function test_a_fixed_sale_may_exceed_ten_thousand_minor_units()
    {
        // The percentage cap is not a cap on money: a $150 sale is 15000 minor units.
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.sale.store'), $this->validPayload([
            'discount_type' => StoreDiscountType::FIXED->value,
            'discount_value' => 15000,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_sales', ['discount_type' => 'fixed', 'discount_value' => 15000]);
    }

    public function test_an_end_date_before_the_start_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.sale.store'), $this->validPayload([
            'starts_at' => now()->addWeek()->toDateTimeString(),
            'ends_at' => now()->toDateTimeString(),
        ]))->assertSessionHasErrors(['ends_at']);
    }

    public function test_scope_rows_are_written_for_packages_and_categories()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $category = StoreCategory::factory()->create();

        $this->post(route('admin.store.sale.store'), $this->validPayload([
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
    }

    public function test_clearing_the_scope_makes_the_sale_store_wide_again()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $sale = StoreSale::factory()->create();
        $sale->saleables()->create([
            'saleable_type' => StorePackage::class,
            'saleable_id' => $package->id,
        ]);

        $this->put(route('admin.store.sale.update', $sale->id), $this->validPayload([
            'name' => $sale->name,
            'packages' => [],
            'categories' => [],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(0, $sale->fresh()->saleables()->count());
    }

    public function test_the_edit_page_preselects_the_current_scope()
    {
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
    }

    public function test_admin_can_update_a_sale()
    {
        $this->actingAs(User::whereId(1)->first());
        $sale = StoreSale::factory()->create(['name' => 'Old Name']);

        $this->put(route('admin.store.sale.update', $sale->id), $this->validPayload([
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
    }

    public function test_admin_can_delete_a_sale()
    {
        $this->actingAs(User::whereId(1)->first());
        $sale = StoreSale::factory()->create();

        $this->delete(route('admin.store.sale.delete', $sale->id))
            ->assertRedirect(route('admin.store.sale.index'));

        $this->assertDatabaseMissing('store_sales', ['id' => $sale->id]);
    }

    public function test_a_created_sale_discounts_the_storefront_immediately()
    {
        // The point of the CRUD: the pricing service reads sales live, so nothing has to be
        // rebuilt or scheduled for a new sale to take effect.
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['price' => 2000]);

        $this->post(route('admin.store.sale.store'), $this->validPayload([
            'packages' => [$package->id],
        ]))->assertSessionHasNoErrors();

        $quote = app(StorePricingService::class)->quote([['package' => $package->fresh(), 'quantity' => 1]]);

        $this->assertEquals(1500, $quote['items'][0]['unit_price']);
        $this->assertEquals('Summer Sale', $quote['items'][0]['sale_name']);
    }

    public function test_a_fixed_sale_amount_converts_into_the_quoted_currency()
    {
        // The sale amount is held in the base currency. Applied raw against a JPY price it would
        // take ¥5 off a ¥3000 package instead of the ¥750 that $5 is worth.
        $jpy = StoreCurrency::factory()->zeroDecimal()->create(); // 1 USD = 150 JPY, no minor unit
        $package = StorePackage::factory()->create(['price' => 2000]);
        StoreSale::factory()->fixed(500)->create(['name' => 'Five Off']);

        $quote = app(StorePricingService::class)->quote([['package' => $package, 'quantity' => 1]], $jpy);

        // $20.00 → ¥3000, less $5.00 → ¥750.
        $this->assertEquals(3000, $quote['items'][0]['unit_price_original']);
        $this->assertEquals(750, $quote['items'][0]['unit_price_original'] - $quote['items'][0]['unit_price']);
        $this->assertEquals('Five Off', $quote['items'][0]['sale_name']);
    }

    public function test_a_fixed_sale_never_takes_a_line_below_zero()
    {
        $this->baseCurrency();
        $package = StorePackage::factory()->create(['price' => 300]);
        StoreSale::factory()->fixed(5000)->create();

        $quote = app(StorePricingService::class)->quote([['package' => $package, 'quantity' => 1]]);

        $this->assertEquals(0, $quote['items'][0]['unit_price']);
        $this->assertEquals(0, $quote['total']);
    }
}

<?php

namespace Tests\Feature\Store;

use App\Models\StoreCategory;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePublicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
    }

    public function test_a_guest_can_browse_the_storefront()
    {
        StorePackage::factory()->count(3)->create();

        $this->get(route('store.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Store/IndexStore', false)
                ->has('packages', 3)
            );
    }

    public function test_disabled_and_hidden_packages_are_excluded_from_listings()
    {
        StorePackage::factory()->create(['name' => 'Visible One']);
        StorePackage::factory()->disabled()->create();
        StorePackage::factory()->hidden()->create();

        $this->get(route('store.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->has('packages', 1)->where('packages.0.name', 'Visible One'));
    }

    public function test_a_hidden_package_is_still_reachable_by_direct_link()
    {
        // Hidden means "unlisted", which is how secret or promo packages work. Disabled means gone.
        $hidden = StorePackage::factory()->hidden()->create();

        $this->get(route('store.package', $hidden->slug))->assertStatus(200);
    }

    public function test_a_disabled_package_is_not_reachable_at_all()
    {
        $disabled = StorePackage::factory()->disabled()->create();

        $this->get(route('store.package', $disabled->slug))->assertStatus(404);
    }

    public function test_a_category_page_lists_only_its_own_packages()
    {
        $category = StoreCategory::factory()->create();
        StorePackage::factory()->count(2)->create(['store_category_id' => $category->id]);
        StorePackage::factory()->create();

        $this->get(route('store.category', $category->slug))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('packages', 2)
                ->where('activeCategory.slug', $category->slug)
            );
    }

    public function test_a_disabled_category_is_not_reachable()
    {
        $category = StoreCategory::factory()->create(['is_enabled' => false]);

        $this->get(route('store.category', $category->slug))->assertStatus(404);
    }

    public function test_prices_are_shipped_both_raw_and_formatted()
    {
        $this->baseCurrency();
        StorePackage::factory()->create(['price' => 999]);

        $this->get(route('store.index'))
            ->assertInertia(fn ($page) => $page
                ->where('packages.0.price', 999)
                ->where('packages.0.price_formatted', '$9.99')
                ->where('currency.current', 'USD')
            );
    }

    public function test_prices_are_shown_in_the_selected_currency()
    {
        $this->baseCurrency();
        StoreCurrency::factory()->zeroDecimal()->create(); // JPY at 150
        StorePackage::factory()->create(['price' => 1000]); // $10.00

        session(['store_currency' => 'JPY']);

        $this->get(route('store.index'))
            ->assertInertia(fn ($page) => $page
                ->where('packages.0.price', 1500)
                ->where('packages.0.price_formatted', '¥1,500')
                ->where('currency.current', 'JPY')
            );
    }

    public function test_out_of_stock_is_flagged()
    {
        StorePackage::factory()->create(['global_purchase_limit' => 5, 'sold_count' => 5]);

        $this->get(route('store.index'))
            ->assertInertia(fn ($page) => $page->where('packages.0.is_out_of_stock', true));
    }

    public function test_every_public_store_route_is_gone_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);
        $package = StorePackage::factory()->create();
        $category = StoreCategory::factory()->create();

        $this->get(route('store.index'))->assertStatus(403);
        $this->get(route('store.category', $category->slug))->assertStatus(403);
        $this->get(route('store.package', $package->slug))->assertStatus(403);
    }

    public function test_an_authenticated_user_can_browse_too()
    {
        StorePackage::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('store.index'))
            ->assertStatus(200);
    }
}

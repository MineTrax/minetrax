<?php

use App\Models\StoreCategory;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Services\StorePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

test('a guest can browse the storefront', function () {
    StorePackage::factory()->count(3)->create();

    $this->get(route('store.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Store/IndexStore', false)
            ->has('packages', 3)
        );
});

test('disabled and hidden packages are excluded from listings', function () {
    StorePackage::factory()->create(['name' => 'Visible One']);
    StorePackage::factory()->disabled()->create();
    StorePackage::factory()->hidden()->create();

    $this->get(route('store.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->has('packages', 1)->where('packages.0.name', 'Visible One'));
});

test('a hidden package is still reachable by direct link', function () {
    // Hidden means "unlisted", which is how secret or promo packages work. Disabled means gone.
    $hidden = StorePackage::factory()->hidden()->create();

    $this->get(route('store.package', $hidden->slug))->assertStatus(200);
});

test('a disabled package is not reachable at all', function () {
    $disabled = StorePackage::factory()->disabled()->create();

    $this->get(route('store.package', $disabled->slug))->assertStatus(404);
});

test('a category page lists only its own packages', function () {
    $category = StoreCategory::factory()->create();
    StorePackage::factory()->count(2)->create(['store_category_id' => $category->id]);
    StorePackage::factory()->create();

    $this->get(route('store.category', $category->slug))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->has('packages', 2)
            ->where('activeCategory.slug', $category->slug)
        );
});

test('a disabled category is not reachable', function () {
    $category = StoreCategory::factory()->create(['is_enabled' => false]);

    $this->get(route('store.category', $category->slug))->assertStatus(404);
});

test('prices are shipped both raw and formatted', function () {
    $this->baseCurrency();
    StorePackage::factory()->create(['price' => 999]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 999)
            ->where('packages.0.price_formatted', '$9.99')
            ->where('currency.current', 'USD')
        );
});

test('prices are shown in the selected currency', function () {
    $this->baseCurrency();
    StoreCurrency::factory()->zeroDecimal()->create();
    // JPY at 150
    StorePackage::factory()->create(['price' => 1000]);

    // $10.00
    session(['store_currency' => 'JPY']);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 1500)
            ->where('packages.0.price_formatted', '¥1,500')
            ->where('currency.current', 'JPY')
        );
});

test('an active sale discounts the storefront price', function () {
    // The listing used to work its own price out and left sales out entirely, so a shopper saw
    // the full price and only found the discount after adding to the cart.
    $this->baseCurrency();
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreSale::factory()->create(['name' => 'MEGA SALE', 'discount_value' => 1500]);

    // 15%
    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 850)
            ->where('packages.0.price_formatted', '$8.50')
            // The undiscounted price still travels, so the card can strike it through.
            ->where('packages.0.price_original', 1000)
            ->where('packages.0.sale_name', 'MEGA SALE')
        );

    $this->get(route('store.package', $package->slug))
        ->assertInertia(fn ($page) => $page
            ->where('storePackage.price', 850)
            ->where('storePackage.sale_name', 'MEGA SALE')
        );
});

test('the sale percentage is reported as configured not derived from the price', function () {
    // 15% of 149 minor units is 22.35, saved as 22, so deriving the percentage back out of the
    // prices reported 14.8% on this package and 14.9% on a $9.99 one. The badge states the sale.
    $this->baseCurrency();
    StorePackage::factory()->create(['price' => 149]);
    StoreSale::factory()->create(['discount_value' => 1500]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 127)
            ->where('packages.0.sale_discount_bp', 1500)
            ->where('packages.0.discount_bp', 0)
            ->where('packages.0.sale_amount_formatted', null)
        );
});

test('a package discount and a sale are reported separately', function () {
    // Both apply, the sale on top of the already reduced price, so they are listed rather than
    // added into a single figure that matches neither.
    $this->baseCurrency();
    StorePackage::factory()->create(['price' => 1000, 'discount_bp' => 1000]);
    // 10% off
    StoreSale::factory()->create(['discount_value' => 1500]);

    // then 15% off
    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            // 1000 less 10% is 900, less 15% of 900 is 765.
            ->where('packages.0.price', 765)
            ->where('packages.0.discount_bp', 1000)
            ->where('packages.0.sale_discount_bp', 1500)
        );
});

test('a fixed amount sale names the money saved instead of a percentage', function () {
    $this->baseCurrency();
    StorePackage::factory()->create(['price' => 2000]);
    StoreSale::factory()->fixed(500)->create();

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 1500)
            // No percentage exists for a fixed sale, so none is invented.
            ->where('packages.0.sale_discount_bp', null)
            ->where('packages.0.sale_amount_formatted', '$5.00')
        );
});

test('a sale scoped elsewhere does not touch a package', function () {
    $this->baseCurrency();
    $other = StorePackage::factory()->create(['price' => 500]);
    StorePackage::factory()->create(['name' => 'Untouched', 'price' => 1000]);

    $sale = StoreSale::factory()->forPackages()->create(['discount_value' => 5000]);
    $sale->saleables()->create(['saleable_type' => StorePackage::class, 'saleable_id' => $other->id]);

    $this->get(route('store.index'))
        ->assertInertia(function ($page) {
            $byName = collect($page->toArray()['props']['packages'])->keyBy('name');

            expect($byName['Untouched']['price'])->toBe(1000);
            expect($byName['Untouched']['sale_name'])->toBeNull();
        });
});

test('a conditional sale does not discount a listed price', function () {
    // A listing has no cart to measure, so pricing a minimum-spend sale into a card would
    // advertise a figure the cart then refuses to honour.
    $this->baseCurrency();
    StorePackage::factory()->create(['name' => 'Rank', 'price' => 1000]);
    StoreSale::factory()->withMinimum(5000)->create(['name' => 'Big Spender', 'discount_value' => 2000]);

    $this->get(route('store.index'))
        ->assertInertia(function ($page) {
            $byName = collect($page->toArray()['props']['packages'])->keyBy('name');

            expect($byName['Rank']['price'])->toBe(1000);
            expect($byName['Rank']['sale_name'])->toBeNull();
        });
});

test('a listing says what a conditional sale would take and what it costs to unlock', function () {
    $this->baseCurrency();
    StorePackage::factory()->create(['name' => 'Rank', 'price' => 1000]);
    StoreSale::factory()->withMinimum(5000)->create(['name' => 'Big Spender', 'discount_value' => 2000]);

    $this->get(route('store.index'))
        ->assertInertia(function ($page) {
            $byName = collect($page->toArray()['props']['packages'])->keyBy('name');

            expect($byName['Rank']['conditional_sale_name'])->toBe('Big Spender');
            expect($byName['Rank']['conditional_sale_discount_bp'])->toBe(2000);
            expect($byName['Rank']['conditional_sale_minimum_formatted'])->toBe('$50.00');
        });
});

test('the storefront price still matches the cart when a conditional sale is unmet', function () {
    // The guard against pricing conditional sales in optimistically: the card and the cart have to
    // agree, and only the cart can measure the basket.
    $this->baseCurrency();
    $package = StorePackage::factory()->create(['price' => 1499]);
    StoreSale::factory()->withMinimum(9000)->create(['discount_value' => 2000]);

    $listed = null;
    $this->get(route('store.index'))->assertInertia(function ($page) use (&$listed) {
        $listed = collect($page->toArray()['props']['packages'])->first()['price'];
    });

    $quote = app(StorePricingService::class)->quote([['package' => $package->fresh(), 'quantity' => 1]]);

    expect($listed)->toBe($quote['items'][0]['unit_price']);
});

test('the storefront price matches what the cart will charge', function () {
    // The point of pricing through StorePricingService: one number, one source.
    $this->baseCurrency();
    $package = StorePackage::factory()->create(['price' => 1499, 'discount_bp' => 1000]);
    StoreSale::factory()->create(['discount_value' => 2000]);

    $shopWindow = null;
    $this->get(route('store.index'))->assertInertia(function ($page) use (&$shopWindow) {
        $shopWindow = $page->toArray()['props']['packages'][0]['price'];
    });

    $quoted = app(StorePricingService::class)
        ->quote([['package' => $package, 'quantity' => 1]])['items'][0]['unit_price'];

    expect($shopWindow)->toBe($quoted);
});

test('a sale never moves a pay what you want price', function () {
    // There is no list price to discount; the figure shown is the floor the buyer must clear.
    $this->baseCurrency();
    StorePackage::factory()->create(['price' => 500, 'is_pay_what_you_want' => true]);
    StoreSale::factory()->create(['discount_value' => 5000]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.price', 500)
            ->where('packages.0.sale_name', null)
        );
});

test('out of stock is flagged', function () {
    StorePackage::factory()->create(['global_purchase_limit' => 5, 'sold_count' => 5]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page->where('packages.0.is_out_of_stock', true));
});

test('every public store route is gone when the module is disabled', function () {
    config(['store.enabled' => false]);
    $package = StorePackage::factory()->create();
    $category = StoreCategory::factory()->create();

    $this->get(route('store.index'))->assertStatus(403);
    $this->get(route('store.category', $category->slug))->assertStatus(403);
    $this->get(route('store.package', $package->slug))->assertStatus(403);
});

test('an authenticated user can browse too', function () {
    StorePackage::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('store.index'))
        ->assertStatus(200);
});

test('remaining stock is reported only when the number is small enough to act on', function () {
    StorePackage::factory()->create(['name' => 'Nearly Gone', 'global_purchase_limit' => 12, 'sold_count' => 9]);
    StorePackage::factory()->create(['name' => 'Plenty Left', 'global_purchase_limit' => 500, 'sold_count' => 10]);
    StorePackage::factory()->create(['name' => 'Unlimited']);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.stock_remaining', 3)
            ->where('packages.1.stock_remaining', null)
            ->where('packages.2.stock_remaining', null)
        );
});

test('a rate limited package reports no remaining stock', function () {
    // A limit that resets is a rate limit, not an inventory, so there is no count to promise.
    StorePackage::factory()->create([
        'global_purchase_limit' => 3,
        'global_purchase_limit_period_days' => 30,
        'sold_count' => 1,
    ]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->where('packages.0.stock_remaining', null)
            ->where('packages.0.is_out_of_stock', false)
        );
});

test('a package page suggests others from the same category', function () {
    $category = StoreCategory::factory()->create();
    $package = StorePackage::factory()->create(['store_category_id' => $category->id]);
    $sibling = StorePackage::factory()->create(['store_category_id' => $category->id]);
    StorePackage::factory()->create();

    $this->get(route('store.package', $package->slug))
        ->assertInertia(fn ($page) => $page
            ->has('relatedPackages', 1)
            ->where('relatedPackages.0.id', $sibling->id)
        );
});

test('a package with no category falls back to featured suggestions', function () {
    $package = StorePackage::factory()->create(['store_category_id' => null]);
    $featured = StorePackage::factory()->create(['store_category_id' => null, 'is_featured' => true]);
    StorePackage::factory()->create(['store_category_id' => null]);

    $this->get(route('store.package', $package->slug))
        ->assertInertia(fn ($page) => $page
            ->has('relatedPackages', 1)
            ->where('relatedPackages.0.id', $featured->id)
        );
});

test('the categories a storefront ships carry their parent so the sidebar can nest them', function () {
    $parent = StoreCategory::factory()->create(['name' => 'Ranks']);
    StoreCategory::factory()->create(['name' => 'Seasonal', 'parent_id' => $parent->id]);

    $this->get(route('store.index'))
        ->assertInertia(fn ($page) => $page
            ->has('categories', 2)
            ->where('categories.1.parent_id', $parent->id)
        );
});

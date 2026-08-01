<?php

use App\Models\Country;
use App\Models\Player;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreTax;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StorePricingService;
use App\Services\StoreTaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
    $this->enableStoreGateways(['manual']);
    Cache::flush();

    $this->withCookie(StoreCartService::COOKIE, 'guest-tax-token');
    $this->withoutMiddleware([ThrottleRequests::class, ThrottleRequestsWithRedis::class]);

    // Keeps the checkout below off the live api.minecraftservices.com lookup: StorePlayerResolver
    // short-circuits on a player it already knows, and Mojang rate-limits that endpoint.
    Player::factory()->create(['username' => 'Steve']);
});

/**
 * A real seeded country. There is no CountryFactory, and there does not need to be: CountrySeeder
 * puts every ISO country in the table before any test runs.
 */
function taxCountry(int $offset = 0): Country
{
    return Country::orderBy('id')->skip($offset)->firstOrFail();
}

/**
 * @return array<string, mixed>
 */
function taxQuoteFor(int $priceMinor, ?int $countryId): array
{
    $package = StorePackage::factory()->create(['price' => $priceMinor]);

    return app(StorePricingService::class)->quote(
        [['package' => $package->fresh(['category', 'prices']), 'quantity' => 1]],
        null, null, null, null, null, $countryId,
    );
}

function placeTaxedOrder(int $priceMinor = 1000): StoreOrder
{
    $package = StorePackage::factory()->create(['price' => $priceMinor]);

    test()->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    test()->post(route('store.checkout.store'), [
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ])->assertSessionHasNoErrors();

    return StoreOrder::latest('id')->firstOrFail();
}

// -- Which rule applies --------------------------------------------------------------------------

test('no rules means no tax', function () {
    $quote = taxQuoteFor(1000, null);

    expect($quote['tax_amount'])->toBe(0);
    expect($quote['total'])->toBe(1000);
    expect($quote['tax_name'])->toBeNull();
});

test('a buyers own country rule beats the global one', function () {
    $spain = taxCountry();
    StoreTax::factory()->create(['name' => 'Global', 'rate_bp' => 500]);
    StoreTax::factory()->forCountry($spain->id)->create(['name' => 'Spain VAT', 'rate_bp' => 2100]);

    $quote = taxQuoteFor(1000, $spain->id);

    expect($quote['tax_name'])->toBe('Spain VAT');
    expect($quote['tax_amount'])->toBe(210);
});

test('a country with no rule of its own falls back to the global rule', function () {
    $spain = taxCountry();
    $germany = taxCountry(1);
    StoreTax::factory()->create(['name' => 'Global', 'rate_bp' => 500]);
    StoreTax::factory()->forCountry($spain->id)->create(['rate_bp' => 2100]);

    expect(taxQuoteFor(1000, $germany->id)['tax_name'])->toBe('Global');
});

test('an unknown country still gets the global rule', function () {
    // A buyer whose IP could not be placed is not a buyer who owes nothing — under-collecting is
    // the store's liability, not theirs.
    StoreTax::factory()->create(['name' => 'Global', 'rate_bp' => 2000]);

    expect(taxQuoteFor(1000, null)['tax_amount'])->toBe(200);
});

test('a disabled rule charges nobody', function () {
    StoreTax::factory()->create(['rate_bp' => 2000, 'is_enabled' => false]);

    expect(taxQuoteFor(1000, null)['tax_amount'])->toBe(0);
});

test('rules never stack', function () {
    // Two rules match this buyer: their country's, and the global fallback. Only one may apply.
    $canada = taxCountry();
    StoreTax::factory()->create(['rate_bp' => 500]);
    StoreTax::factory()->forCountry($canada->id)->create(['rate_bp' => 500]);

    expect(taxQuoteFor(1000, $canada->id)['tax_amount'])->toBe(50, 'Not 100.');
});

// -- Inclusive and exclusive ----------------------------------------------------------------------

test('an exclusive rule adds to the total', function () {
    StoreTax::factory()->create(['rate_bp' => 2000]);

    $quote = taxQuoteFor(1000, null);

    expect($quote['tax_amount'])->toBe(200);
    expect($quote['total'])->toBe(1200);
    expect($quote['tax_is_inclusive'])->toBeFalse();
});

test('an inclusive rule leaves the total alone', function () {
    // The advertised price already contained it, so breaking it out must not change what is paid.
    StoreTax::factory()->inclusive()->create(['rate_bp' => 2100]);

    $quote = taxQuoteFor(12100, null);

    expect($quote['tax_amount'])->toBe(2100, 'Extracted from the price, not added to it.');
    expect($quote['total'])->toBe(12100);
    expect($quote['tax_is_inclusive'])->toBeTrue();
});

test('tax is charged on what the buyer actually pays', function () {
    StoreTax::factory()->create(['rate_bp' => 2000]);
    $package = StorePackage::factory()->create(['price' => 1000, 'discount_bp' => 5000]);

    $quote = app(StorePricingService::class)->quote(
        [['package' => $package->fresh(['category', 'prices']), 'quantity' => 1]],
        null, null, null, null, null, null,
    );

    expect($quote['subtotal'])->toBe(500);
    expect($quote['tax_amount'])->toBe(100, '20% of 500, not of 1000.');
});

test('rounding never charges more than the rate', function () {
    StoreTax::factory()->create(['rate_bp' => 2000]);

    // 20% of 999 is 199.8, floored — a buyer is never rounded up into.
    expect(taxQuoteFor(999, null)['tax_amount'])->toBe(199);
});

// -- The order snapshot ----------------------------------------------------------------------------

test('an order records the rate it was charged', function () {
    StoreTax::factory()->create(['name' => 'VAT', 'rate_bp' => 2000]);

    $order = placeTaxedOrder();

    expect($order->tax_name)->toBe('VAT');
    expect($order->tax_rate_bp)->toBe(2000);
    expect((int) $order->tax_amount)->toBe(200);
});

test('changing a rule does not rewrite an order already placed', function () {
    // The whole reason the rate is snapshotted onto the order.
    $rule = StoreTax::factory()->create(['name' => 'VAT', 'rate_bp' => 2000]);
    $order = placeTaxedOrder();

    $rule->update(['rate_bp' => 2500, 'name' => 'VAT raised']);

    expect($order->fresh()->tax_rate_bp)->toBe(2000);
    expect($order->fresh()->tax_name)->toBe('VAT');
});

// -- Admin ------------------------------------------------------------------------------------------

test('a guest cannot reach the tax admin', function () {
    $this->get(route('admin.store.tax.index'))->assertRedirect(route('login'));
});

test('a superadmin can create a country rule', function () {
    $spain = taxCountry();

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.tax.store'), [
            'name' => 'Spain VAT',
            'country_id' => $spain->id,
            'rate_bp' => 2100,
            'is_inclusive' => true,
            'is_enabled' => true,
        ])->assertRedirect(route('admin.store.tax.index'));

    $tax = StoreTax::firstOrFail();

    expect($tax->rate_bp)->toBe(2100);
    expect($tax->is_inclusive)->toBeTrue();
    expect($tax->country_id)->toBe($spain->id);
});

test('a country cannot have two rules', function () {
    $spain = taxCountry();
    StoreTax::factory()->forCountry($spain->id)->create();

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.tax.store'), [
            'name' => 'Another', 'country_id' => $spain->id,
            'rate_bp' => 1000, 'is_inclusive' => false, 'is_enabled' => true,
        ])->assertSessionHasErrors('country_id');
});

test('there can only be one global rule', function () {
    // SQL treats every NULL as distinct, so the unique index alone would not catch this.
    StoreTax::factory()->create();

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.tax.store'), [
            'name' => 'Second global', 'country_id' => null,
            'rate_bp' => 1000, 'is_inclusive' => false, 'is_enabled' => true,
        ])->assertSessionHasErrors('country_id');
});

test('a rate above one hundred percent is refused', function () {
    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.store.tax.store'), [
            'name' => 'Typo', 'country_id' => null,
            'rate_bp' => 10001, 'is_inclusive' => false, 'is_enabled' => true,
        ])->assertSessionHasErrors('rate_bp');
});

test('editing a rule takes effect on the next quote', function () {
    // Rules are cached, so a corrected rate has to invalidate it or buyers keep paying the old one.
    $rule = StoreTax::factory()->create(['rate_bp' => 1000]);
    expect(taxQuoteFor(1000, null)['tax_amount'])->toBe(100);

    $this->actingAs(User::whereId(1)->first())
        ->put(route('admin.store.tax.update', $rule->id), [
            'name' => $rule->name, 'country_id' => null,
            'rate_bp' => 2000, 'is_inclusive' => false, 'is_enabled' => true,
        ])->assertRedirect();

    expect(taxQuoteFor(1000, null)['tax_amount'])->toBe(200);
});

test('deleting a rule stops it applying', function () {
    $rule = StoreTax::factory()->create(['rate_bp' => 2000]);

    $this->actingAs(User::whereId(1)->first())
        ->delete(route('admin.store.tax.delete', $rule->id))->assertRedirect();

    expect(taxQuoteFor(1000, null)['tax_amount'])->toBe(0);
});

test('the service resolves a rule without a global fallback', function () {
    $spain = taxCountry();
    StoreTax::factory()->forCountry($spain->id)->create(['rate_bp' => 2100]);

    $service = app(StoreTaxService::class);

    expect($service->resolveFor($spain->id)?->rate_bp)->toBe(2100);
    expect($service->resolveFor(null))->toBeNull('No global rule exists.');
});

test('the admin screens render', function () {
    // Not redundant with the write tests above: those never touch the listing, which is where a
    // bad allowedFilters() call blew up at runtime while every other test stayed green.
    $this->actingAs(User::whereId(1)->first());
    StoreTax::factory()->create(['name' => 'Global VAT']);

    $this->get(route('admin.store.tax.index'))->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreTax/IndexStoreTax')
            ->has('taxes.data', 1)
        );

    $this->get(route('admin.store.tax.index', ['filter' => ['q' => 'VAT'], 'sort' => '-rate_bp']))
        ->assertOk();

    $this->get(route('admin.store.tax.create'))->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreTax/CreateStoreTax')
            // The select is built from these; without them the dropdown is empty.
            ->has('countries.0.name')
        );
});

test('the edit screen carries the country the rule is bound to', function () {
    // Null and a real id are different shapes, and the select has to preselect both.
    $this->actingAs(User::whereId(1)->first());
    $spain = taxCountry();

    $global = StoreTax::factory()->create();
    $country = StoreTax::factory()->forCountry($spain->id)->create();

    $this->get(route('admin.store.tax.edit', $global->id))
        ->assertInertia(fn ($page) => $page->where('storeTax.country_id', null));

    $this->get(route('admin.store.tax.edit', $country->id))
        ->assertInertia(fn ($page) => $page->where('storeTax.country_id', $spain->id));
});

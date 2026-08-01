<?php

use App\Enums\StoreCategoryDisplayType;
use App\Enums\StorePackageGrantStatus;
use App\Models\Player;
use App\Models\StoreCategory;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreCurrencyService;
use App\Services\StorePricingService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = ['manual'];
    $settings->save();

    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

    $this->withoutMiddleware([
        ThrottleRequests::class,
        ThrottleRequestsWithRedis::class,
    ]);
});

/**
 * @return array<string, mixed>
 */
function categoryPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ranks',
        'description' => null,
        'parent_id' => null,
        'sort_order' => 0,
        'is_visible' => true,
        'is_enabled' => true,
        'display_type' => StoreCategoryDisplayType::GRID->value,
        'comparison_fields' => [],
        'is_cumulative' => false,
    ], $overrides);
}

function comparisonCategory(array $fields): StoreCategory
{
    return StoreCategory::factory()->create([
        'display_type' => StoreCategoryDisplayType::COMPARISON,
        'comparison_fields' => $fields,
    ]);
}

test('a category defaults to the grid layout', function () {
    expect(StoreCategory::factory()->create()->display_type)->toEqual(StoreCategoryDisplayType::GRID);
});

test('admin can choose a display type', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryPayload([
        'display_type' => StoreCategoryDisplayType::LISTING->value,
    ]))->assertSessionHasNoErrors();

    expect(StoreCategory::first()->display_type)->toEqual(StoreCategoryDisplayType::LISTING);
});

test('an unknown display type is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryPayload([
        'display_type' => 'carousel',
    ]))->assertSessionHasErrors(['display_type']);
});

test('admin can define comparison fields', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryPayload([
        'display_type' => StoreCategoryDisplayType::COMPARISON->value,
        'comparison_fields' => [
            ['key' => 'field_1', 'name' => 'Coins', 'description' => 'no of coins', 'type' => 'text'],
            ['key' => 'field_2', 'name' => 'Pro', 'description' => 'is pro?', 'type' => 'check'],
        ],
    ]))->assertSessionHasNoErrors();

    $fields = StoreCategory::first()->comparisonFields();

    expect($fields)->toHaveCount(2);
    expect($fields[0]['name'])->toBe('Coins');
    expect($fields[1]['type'])->toBe('check');
});

test('a comparison field must have a name', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryPayload([
        'display_type' => StoreCategoryDisplayType::COMPARISON->value,
        'comparison_fields' => [
            ['key' => 'field_1', 'name' => '', 'description' => null, 'type' => 'text'],
        ],
    ]))->assertSessionHasErrors(['comparison_fields.0.name']);
});

test('two comparison fields cannot share a key', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryPayload([
        'display_type' => StoreCategoryDisplayType::COMPARISON->value,
        'comparison_fields' => [
            ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
            ['key' => 'field_1', 'name' => 'Bonus', 'description' => null, 'type' => 'text'],
        ],
    ]))->assertSessionHasErrors(['comparison_fields.0.key']);
});

test('renaming a field keeps the values packages hold against it', function () {
    // The key is what a package's cell is filed under, so a rename must not orphan it.
    $this->actingAs(User::whereId(1)->first());
    $category = comparisonCategory([
        ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
    ]);
    $package = StorePackage::factory()->create([
        'store_category_id' => $category->id,
        'comparison_values' => ['field_1' => '1000'],
    ]);

    $this->put(route('admin.store.category.update', $category->id), categoryPayload([
        'name' => $category->name,
        'display_type' => StoreCategoryDisplayType::COMPARISON->value,
        'comparison_fields' => [
            ['key' => 'field_1', 'name' => 'Gold Coins', 'description' => null, 'type' => 'text'],
        ],
    ]))->assertSessionHasNoErrors();

    expect($category->fresh()->comparisonFields()[0]['name'])->toBe('Gold Coins');
    expect($package->fresh()->comparison_values)->toBe(['field_1' => '1000']);
});

test('comparison fields are ignored by a category that does not use them', function () {
    // Kept in the column so switching back does not lose them, but never read as a table.
    $category = StoreCategory::factory()->create([
        'display_type' => StoreCategoryDisplayType::GRID,
        'comparison_fields' => [
            ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
        ],
    ]);

    expect($category->comparisonFields())->toBe([]);
    expect($category->comparison_fields)->not->toBeNull();
});

test('a half written field never reaches the storefront', function () {
    $category = comparisonCategory([
        ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
        ['key' => '', 'name' => 'Nameless', 'description' => null, 'type' => 'text'],
        ['name' => 'Keyless', 'type' => 'text'],
    ]);

    expect($category->comparisonFields())->toHaveCount(1);
});

test('a package stores only the cells its category defines', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = comparisonCategory([
        ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
    ]);

    $this->post(route('admin.store.package.store'), [
        'name' => 'Gold Rank',
        'store_category_id' => $category->id,
        'type' => 'minecraft_package',
        'price' => 1000,
        'is_pay_what_you_want' => false,
        'is_gift_card_amount_same_as_price' => false,
        'is_visible' => true, 'is_enabled' => true, 'requires_login' => false,
        'is_featured' => false, 'is_giftable' => false,
        'min_quantity' => 1,
        'required_packages_mode' => 'all',
        'comparison_values' => ['field_1' => '1000', 'not_a_field' => 'injected'],
    ])->assertSessionHasNoErrors();

    expect(StorePackage::first()->comparison_values)->toBe(['field_1' => '1000']);
});

// --- Storefront -------------------------------------------------------------------------------
function visitCategory(StoreCategory $category): TestResponse
{
    return test()->get(route('store.category', $category->slug))->assertOk();
}

test('the category page reports its display type', function () {
    $category = StoreCategory::factory()->create(['display_type' => StoreCategoryDisplayType::STACKED]);
    StorePackage::factory()->create(['store_category_id' => $category->id]);

    visitCategory($category)
        ->assertInertia(fn ($page) => $page->where('activeCategory.display_type.value', 'stacked'));
});

test('the comparison table gets a cell for every field even when unfilled', function () {
    // Missing cells are nulls in the right slots, so a field added later cannot shift a column.
    $category = comparisonCategory([
        ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
        ['key' => 'field_2', 'name' => 'Pro', 'description' => null, 'type' => 'check'],
    ]);
    StorePackage::factory()->create([
        'store_category_id' => $category->id,
        'comparison_values' => ['field_1' => '1000'],
    ]);

    visitCategory($category)
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['activeCategory']['comparison_fields'])->toHaveCount(2);
            expect($props['packages'][0]['comparison_values'])->toBe(['field_1' => '1000', 'field_2' => null]);
        });
});

test('a grid category ships no comparison values', function () {
    $category = StoreCategory::factory()->create();
    StorePackage::factory()->create(['store_category_id' => $category->id]);

    visitCategory($category)
        ->assertInertia(fn ($page) => $page->missing('packages.0.comparison_values'));
});

test('a package needing configuration is flagged for the listings', function () {
    // Every layout offers an add to cart button, which cannot work for a package that has to be
    // priced or answered first — those get a link to their own page instead.
    $plain = StorePackage::factory()->create(['name' => 'Plain']);
    $pwyw = StorePackage::factory()->payWhatYouWant()->create(['name' => 'Donation']);

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(function ($page) use ($plain, $pwyw) {
            $packages = collect($page->toArray()['props']['packages'])->keyBy('id');

            expect($packages[$plain->id]['needs_configuring'])->toBeFalse();
            expect($packages[$pwyw->id]['needs_configuring'])->toBeTrue();
        });
});

test('the listings carry what an add to cart button has to decide with', function () {
    // The four layouts all render the button from these three fields. Losing one of them from the
    // payload would not break the page — it would quietly offer to sell an out of stock package.
    StorePackage::factory()->create(['min_quantity' => 3]);

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('packages.0.needs_configuring')
            ->has('packages.0.is_out_of_stock')
            ->where('packages.0.min_quantity', 3)
        );
});

test('adding straight from a listing starts at the package minimum', function () {
    // The listing button has no quantity picker, so it posts the package's own minimum rather
    // than 1 and leaving the server to silently bump it.
    $package = StorePackage::factory()->create(['min_quantity' => 5, 'max_quantity' => 10]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 5])
        ->assertRedirect(route('store.cart.show'));

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.items.0.quantity', 5));
});

// --- Cumulative upgrade pricing -----------------------------------------------------------
function cumulativeCategory(): StoreCategory
{
    return StoreCategory::factory()->create(['is_cumulative' => true]);
}

function grantTo(string $playerUuid, StorePackage $package, StorePackageGrantStatus $status = StorePackageGrantStatus::ACTIVE): void
{
    $order = StoreOrder::factory()->completed()->create(['player_uuid' => $playerUuid]);
    $item = $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => $package->price,
        'unit_price' => $package->price,
        'total' => $package->price,
    ]);
    $item->grant()->create([
        'store_package_id' => $package->id,
        'player_uuid' => $playerUuid,
        'status' => $status,
        'granted_at' => now(),
    ]);
}

/**
 * @return array<string, mixed>
 */
function categoryDisplayQuote(StorePackage $package, ?string $playerUuid, int $quantity = 1): array
{
    return app(StorePricingService::class)->quote(
        [['package' => $package->fresh(['category', 'prices']), 'quantity' => $quantity]],
        null,
        null,
        null,
        null,
        $playerUuid,
    );
}

test('owning a cheaper package credits its price against a dearer one', function () {
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);

    $quote = categoryDisplayQuote($gold, $player->uuid);

    expect($quote['upgrade_credit'])->toBe(1000);
    expect($quote['total'])->toBe(2000, 'The buyer pays the difference.');
});

test('the dearest owned package is the one credited', function () {
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $bronze = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 500]);
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $bronze);
    grantTo($player->uuid, $silver);

    expect(categoryDisplayQuote($gold, $player->uuid)['upgrade_credit'])->toBe(1000);
});

test('a downgrade earns no credit', function () {
    // Crediting here would hand the cheaper package over for nothing.
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $gold);

    $quote = categoryDisplayQuote($silver, $player->uuid);

    expect($quote['upgrade_credit'])->toBe(0);
    expect($quote['total'])->toBe(1000);
});

test('buying the same package again earns no credit', function () {
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);

    grantTo($player->uuid, $silver);

    expect(categoryDisplayQuote($silver, $player->uuid)['upgrade_credit'])->toBe(0);
});

test('a non cumulative category never credits', function () {
    $category = StoreCategory::factory()->create(['is_cumulative' => false]);
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);

    expect(categoryDisplayQuote($gold, $player->uuid)['upgrade_credit'])->toBe(0);
});

test('a package owned in another category never credits', function () {
    $ranks = cumulativeCategory();
    $keys = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $keys->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $ranks->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);

    expect(categoryDisplayQuote($gold, $player->uuid)['upgrade_credit'])->toBe(0);
});

test('a revoked grant withdraws the credit', function () {
    // A refund revokes the grant, so the credit it earned has to go with it.
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver, StorePackageGrantStatus::REVOKED);

    expect(categoryDisplayQuote($gold, $player->uuid)['upgrade_credit'])->toBe(0);
});

test('a credit is given once per line however many are bought', function () {
    // The buyer holds the cheaper package once, so it is worth crediting once.
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create([
        'store_category_id' => $category->id, 'price' => 3000, 'max_quantity' => 5,
    ]);

    grantTo($player->uuid, $silver);

    $quote = categoryDisplayQuote($gold, $player->uuid, quantity: 3);

    expect($quote['upgrade_credit'])->toBe(1000);
    expect($quote['total'])->toBe(8000, '3 x 3000, less one credit of 1000.');
});

test('a credit never takes a line below zero', function () {
    // A price cut after the cheaper package was bought must not owe the buyer money.
    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);
    $gold->update(['discount_bp' => 10000]);

    // now free
    $quote = categoryDisplayQuote($gold, $player->uuid);

    expect($quote['total'])->toBe(0);
    expect($quote['upgrade_credit'])->toBeGreaterThanOrEqual(0);
});

test('a credit is converted into the currency being quoted', function () {
    StoreCurrency::factory()->create([
        'code' => 'EUR', 'exponent' => 2, 'symbol' => '€', 'is_base' => false, 'rate_to_base' => 2,
    ]);

    $category = cumulativeCategory();
    $player = Player::factory()->create();
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);

    $euro = app(StoreCurrencyService::class)->find('EUR');
    $quote = app(StorePricingService::class)->quote(
        [['package' => $gold->fresh(['category', 'prices']), 'quantity' => 1]],
        $euro,
        null,
        null,
        null,
        $player->uuid,
    );

    // At 2 EUR per USD: a €60 package, less the €20 already spent.
    expect($quote['upgrade_credit'])->toBe(2000);
    expect($quote['total'])->toBe(4000);
});

test('no credit without a player to credit', function () {
    $category = cumulativeCategory();
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    expect(categoryDisplayQuote($gold, null)['upgrade_credit'])->toBe(0);
});

test('checkout credits against the player being delivered to', function () {
    $category = cumulativeCategory();
    $player = Player::factory()->create(['username' => 'Steve']);
    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    grantTo($player->uuid, $silver);

    $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);
    $this->post(route('store.checkout.store'), [
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ])->assertSessionHasNoErrors();

    $order = StoreOrder::latest('id')->first();

    expect((int) $order->total)->toBe(2000);
    expect((int) $order->items->first()->upgrade_credit)->toBe(1000);
});

test('a guest cart shows no credit because there is no player yet', function () {
    // The delivery player is not named until checkout, which is where the real figure comes
    // from. The cart shows the undiscounted price rather than guessing.
    $category = cumulativeCategory();
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

    $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.upgrade_credit', 0)
            ->where('quote.total', 3000)
        );
});

test('a signed in buyer with one linked player sees the credit in the cart', function () {
    $category = cumulativeCategory();
    $user = User::factory()->create();
    $player = Player::factory()->create();
    $user->players()->attach($player->id);

    $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
    $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);
    grantTo($player->uuid, $silver);

    $this->actingAs($user);
    $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.upgrade_credit', 1000)
            ->where('quote.total', 2000)
        );
});

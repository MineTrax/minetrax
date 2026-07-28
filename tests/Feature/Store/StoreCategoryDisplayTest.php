<?php

namespace Tests\Feature\Store;

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
use Tests\TestCase;

/**
 * Category display types and cumulative upgrade pricing.
 */
class StoreCategoryDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryPayload(array $overrides = []): array
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

    private function comparisonCategory(array $fields): StoreCategory
    {
        return StoreCategory::factory()->create([
            'display_type' => StoreCategoryDisplayType::COMPARISON,
            'comparison_fields' => $fields,
        ]);
    }

    // --- Admin: display type ---------------------------------------------------------------------

    public function test_a_category_defaults_to_the_grid_layout()
    {
        $this->assertEquals(StoreCategoryDisplayType::GRID, StoreCategory::factory()->create()->display_type);
    }

    public function test_admin_can_choose_a_display_type()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.category.store'), $this->categoryPayload([
            'display_type' => StoreCategoryDisplayType::LISTING->value,
        ]))->assertSessionHasNoErrors();

        $this->assertEquals(StoreCategoryDisplayType::LISTING, StoreCategory::first()->display_type);
    }

    public function test_an_unknown_display_type_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.category.store'), $this->categoryPayload([
            'display_type' => 'carousel',
        ]))->assertSessionHasErrors(['display_type']);
    }

    // --- Admin: comparison fields -----------------------------------------------------------------

    public function test_admin_can_define_comparison_fields()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.category.store'), $this->categoryPayload([
            'display_type' => StoreCategoryDisplayType::COMPARISON->value,
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => 'Coins', 'description' => 'no of coins', 'type' => 'text'],
                ['key' => 'field_2', 'name' => 'Pro', 'description' => 'is pro?', 'type' => 'check'],
            ],
        ]))->assertSessionHasNoErrors();

        $fields = StoreCategory::first()->comparisonFields();

        $this->assertCount(2, $fields);
        $this->assertSame('Coins', $fields[0]['name']);
        $this->assertSame('check', $fields[1]['type']);
    }

    public function test_a_comparison_field_must_have_a_name()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.category.store'), $this->categoryPayload([
            'display_type' => StoreCategoryDisplayType::COMPARISON->value,
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => '', 'description' => null, 'type' => 'text'],
            ],
        ]))->assertSessionHasErrors(['comparison_fields.0.name']);
    }

    public function test_two_comparison_fields_cannot_share_a_key()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.category.store'), $this->categoryPayload([
            'display_type' => StoreCategoryDisplayType::COMPARISON->value,
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
                ['key' => 'field_1', 'name' => 'Bonus', 'description' => null, 'type' => 'text'],
            ],
        ]))->assertSessionHasErrors(['comparison_fields.0.key']);
    }

    public function test_renaming_a_field_keeps_the_values_packages_hold_against_it()
    {
        // The key is what a package's cell is filed under, so a rename must not orphan it.
        $this->actingAs(User::whereId(1)->first());
        $category = $this->comparisonCategory([
            ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
        ]);
        $package = StorePackage::factory()->create([
            'store_category_id' => $category->id,
            'comparison_values' => ['field_1' => '1000'],
        ]);

        $this->put(route('admin.store.category.update', $category->id), $this->categoryPayload([
            'name' => $category->name,
            'display_type' => StoreCategoryDisplayType::COMPARISON->value,
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => 'Gold Coins', 'description' => null, 'type' => 'text'],
            ],
        ]))->assertSessionHasNoErrors();

        $this->assertSame('Gold Coins', $category->fresh()->comparisonFields()[0]['name']);
        $this->assertSame(['field_1' => '1000'], $package->fresh()->comparison_values);
    }

    public function test_comparison_fields_are_ignored_by_a_category_that_does_not_use_them()
    {
        // Kept in the column so switching back does not lose them, but never read as a table.
        $category = StoreCategory::factory()->create([
            'display_type' => StoreCategoryDisplayType::GRID,
            'comparison_fields' => [
                ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
            ],
        ]);

        $this->assertSame([], $category->comparisonFields());
        $this->assertNotNull($category->comparison_fields);
    }

    public function test_a_half_written_field_never_reaches_the_storefront()
    {
        $category = $this->comparisonCategory([
            ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
            ['key' => '', 'name' => 'Nameless', 'description' => null, 'type' => 'text'],
            ['name' => 'Keyless', 'type' => 'text'],
        ]);

        $this->assertCount(1, $category->comparisonFields());
    }

    // --- Admin: per-package cells -------------------------------------------------------------

    public function test_a_package_stores_only_the_cells_its_category_defines()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = $this->comparisonCategory([
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

        $this->assertSame(['field_1' => '1000'], StorePackage::first()->comparison_values);
    }

    // --- Storefront -------------------------------------------------------------------------------

    private function visitCategory(StoreCategory $category): TestResponse
    {
        return $this->get(route('store.category', $category->slug))->assertOk();
    }

    public function test_the_category_page_reports_its_display_type()
    {
        $category = StoreCategory::factory()->create(['display_type' => StoreCategoryDisplayType::STACKED]);
        StorePackage::factory()->create(['store_category_id' => $category->id]);

        $this->visitCategory($category)
            ->assertInertia(fn ($page) => $page->where('activeCategory.display_type.value', 'stacked'));
    }

    public function test_the_comparison_table_gets_a_cell_for_every_field_even_when_unfilled()
    {
        // Missing cells are nulls in the right slots, so a field added later cannot shift a column.
        $category = $this->comparisonCategory([
            ['key' => 'field_1', 'name' => 'Coins', 'description' => null, 'type' => 'text'],
            ['key' => 'field_2', 'name' => 'Pro', 'description' => null, 'type' => 'check'],
        ]);
        StorePackage::factory()->create([
            'store_category_id' => $category->id,
            'comparison_values' => ['field_1' => '1000'],
        ]);

        $this->visitCategory($category)
            ->assertInertia(function ($page) {
                $props = $page->toArray()['props'];

                $this->assertCount(2, $props['activeCategory']['comparison_fields']);
                $this->assertSame(
                    ['field_1' => '1000', 'field_2' => null],
                    $props['packages'][0]['comparison_values']
                );
            });
    }

    public function test_a_grid_category_ships_no_comparison_values()
    {
        $category = StoreCategory::factory()->create();
        StorePackage::factory()->create(['store_category_id' => $category->id]);

        $this->visitCategory($category)
            ->assertInertia(fn ($page) => $page->missing('packages.0.comparison_values'));
    }

    public function test_a_package_needing_configuration_is_flagged_for_the_listings()
    {
        // The stacked layout adds to the cart in one click, which cannot work for a package that
        // has to be priced or answered first.
        $plain = StorePackage::factory()->create(['name' => 'Plain']);
        $pwyw = StorePackage::factory()->payWhatYouWant()->create(['name' => 'Donation']);

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(function ($page) use ($plain, $pwyw) {
                $packages = collect($page->toArray()['props']['packages'])->keyBy('id');

                $this->assertFalse($packages[$plain->id]['needs_configuring']);
                $this->assertTrue($packages[$pwyw->id]['needs_configuring']);
            });
    }

    // --- Cumulative upgrade pricing -----------------------------------------------------------

    private function cumulativeCategory(): StoreCategory
    {
        return StoreCategory::factory()->create(['is_cumulative' => true]);
    }

    private function grantTo(string $playerUuid, StorePackage $package, StorePackageGrantStatus $status = StorePackageGrantStatus::ACTIVE): void
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
    private function quote(StorePackage $package, ?string $playerUuid, int $quantity = 1): array
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

    public function test_owning_a_cheaper_package_credits_its_price_against_a_dearer_one()
    {
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);

        $quote = $this->quote($gold, $player->uuid);

        $this->assertSame(1000, $quote['upgrade_credit']);
        $this->assertSame(2000, $quote['total'], 'The buyer pays the difference.');
    }

    public function test_the_dearest_owned_package_is_the_one_credited()
    {
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $bronze = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 500]);
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $bronze);
        $this->grantTo($player->uuid, $silver);

        $this->assertSame(1000, $this->quote($gold, $player->uuid)['upgrade_credit']);
    }

    public function test_a_downgrade_earns_no_credit()
    {
        // Crediting here would hand the cheaper package over for nothing.
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $gold);

        $quote = $this->quote($silver, $player->uuid);

        $this->assertSame(0, $quote['upgrade_credit']);
        $this->assertSame(1000, $quote['total']);
    }

    public function test_buying_the_same_package_again_earns_no_credit()
    {
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);

        $this->grantTo($player->uuid, $silver);

        $this->assertSame(0, $this->quote($silver, $player->uuid)['upgrade_credit']);
    }

    public function test_a_non_cumulative_category_never_credits()
    {
        $category = StoreCategory::factory()->create(['is_cumulative' => false]);
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);

        $this->assertSame(0, $this->quote($gold, $player->uuid)['upgrade_credit']);
    }

    public function test_a_package_owned_in_another_category_never_credits()
    {
        $ranks = $this->cumulativeCategory();
        $keys = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $keys->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $ranks->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);

        $this->assertSame(0, $this->quote($gold, $player->uuid)['upgrade_credit']);
    }

    public function test_a_revoked_grant_withdraws_the_credit()
    {
        // A refund revokes the grant, so the credit it earned has to go with it.
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver, StorePackageGrantStatus::REVOKED);

        $this->assertSame(0, $this->quote($gold, $player->uuid)['upgrade_credit']);
    }

    public function test_a_credit_is_given_once_per_line_however_many_are_bought()
    {
        // The buyer holds the cheaper package once, so it is worth crediting once.
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create([
            'store_category_id' => $category->id, 'price' => 3000, 'max_quantity' => 5,
        ]);

        $this->grantTo($player->uuid, $silver);

        $quote = $this->quote($gold, $player->uuid, quantity: 3);

        $this->assertSame(1000, $quote['upgrade_credit']);
        $this->assertSame(8000, $quote['total'], '3 x 3000, less one credit of 1000.');
    }

    public function test_a_credit_never_takes_a_line_below_zero()
    {
        // A price cut after the cheaper package was bought must not owe the buyer money.
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);
        $gold->update(['discount_bp' => 10000]); // now free

        $quote = $this->quote($gold, $player->uuid);

        $this->assertSame(0, $quote['total']);
        $this->assertGreaterThanOrEqual(0, $quote['upgrade_credit']);
    }

    public function test_a_credit_is_converted_into_the_currency_being_quoted()
    {
        StoreCurrency::factory()->create([
            'code' => 'EUR', 'exponent' => 2, 'symbol' => '€', 'is_base' => false, 'rate_to_base' => 2,
        ]);

        $category = $this->cumulativeCategory();
        $player = Player::factory()->create();
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);

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
        $this->assertSame(2000, $quote['upgrade_credit']);
        $this->assertSame(4000, $quote['total']);
    }

    public function test_no_credit_without_a_player_to_credit()
    {
        $category = $this->cumulativeCategory();
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->assertSame(0, $this->quote($gold, null)['upgrade_credit']);
    }

    public function test_checkout_credits_against_the_player_being_delivered_to()
    {
        $category = $this->cumulativeCategory();
        $player = Player::factory()->create(['username' => 'Steve']);
        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->grantTo($player->uuid, $silver);

        $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);
        $this->post(route('store.checkout.store'), [
            'player_username' => 'Steve',
            'email' => 'buyer@example.com',
            'gateway' => 'manual',
            'accept_terms' => true,
        ])->assertSessionHasNoErrors();

        $order = StoreOrder::latest('id')->first();

        $this->assertSame(2000, (int) $order->total);
        $this->assertSame(1000, (int) $order->items->first()->upgrade_credit);
    }

    public function test_a_guest_cart_shows_no_credit_because_there_is_no_player_yet()
    {
        // The delivery player is not named until checkout, which is where the real figure comes
        // from. The cart shows the undiscounted price rather than guessing.
        $category = $this->cumulativeCategory();
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);

        $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page
                ->where('quote.upgrade_credit', 0)
                ->where('quote.total', 3000)
            );
    }

    public function test_a_signed_in_buyer_with_one_linked_player_sees_the_credit_in_the_cart()
    {
        $category = $this->cumulativeCategory();
        $user = User::factory()->create();
        $player = Player::factory()->create();
        $user->players()->attach($player->id);

        $silver = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 1000]);
        $gold = StorePackage::factory()->create(['store_category_id' => $category->id, 'price' => 3000]);
        $this->grantTo($player->uuid, $silver);

        $this->actingAs($user);
        $this->post(route('store.cart.store'), ['package_id' => $gold->id, 'quantity' => 1]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page
                ->where('quote.upgrade_credit', 1000)
                ->where('quote.total', 2000)
            );
    }
}

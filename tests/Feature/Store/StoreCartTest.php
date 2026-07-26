<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDiscountType;
use App\Models\StoreCart;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();

        // A real browser returns the cart cookie on every request; the test client does not carry
        // queued cookies forward, so it is pinned here for the whole test. withCookie (not
        // withUnencryptedCookie) so the value is encrypted the way EncryptCookies expects.
        $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');
    }

    private function packageWithOption(): array
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $option = $package->options()->create([
            'name' => 'Tier', 'placeholder' => 'TIER', 'type' => 'select', 'is_required' => true, 'sort_order' => 0,
        ]);
        $gold = $option->choices()->create(['name' => 'Gold', 'value' => 'gold', 'price_delta' => 0, 'is_enabled' => true, 'sort_order' => 0]);
        $diamond = $option->choices()->create(['name' => 'Diamond', 'value' => 'diamond', 'price_delta' => 500, 'is_enabled' => true, 'sort_order' => 1]);

        return [$package, $gold, $diamond];
    }

    public function test_a_guest_can_add_a_package_to_a_cart()
    {
        $package = StorePackage::factory()->create(['price' => 999]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2])
            ->assertRedirect(route('store.cart.show'));

        $this->assertDatabaseCount('store_carts', 1);
        $this->assertDatabaseHas('store_cart_items', ['store_package_id' => $package->id, 'quantity' => 2]);
    }

    public function test_a_guest_cart_is_keyed_on_a_cookie_token()
    {
        $package = StorePackage::factory()->create();

        $response = $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $response->assertCookie(StoreCartService::COOKIE);
        $this->assertNotNull(StoreCart::first()->session_token);
        $this->assertNull(StoreCart::first()->user_id);
    }

    public function test_a_logged_in_user_gets_exactly_one_cart()
    {
        $user = User::factory()->create();
        $package = StorePackage::factory()->create();

        $this->actingAs($user)->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->actingAs($user)->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->assertDatabaseCount('store_carts', 1);
        $this->assertEquals($user->id, StoreCart::first()->user_id);
    }

    public function test_adding_the_same_package_with_the_same_options_merges_into_one_line()
    {
        [$package, $gold] = $this->packageWithOption();

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1, 'choices' => [$gold->id]]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2, 'choices' => [$gold->id]]);

        $this->assertDatabaseCount('store_cart_items', 1);
        $this->assertDatabaseHas('store_cart_items', ['quantity' => 3]);
    }

    public function test_the_same_package_with_different_options_makes_separate_lines()
    {
        [$package, $gold, $diamond] = $this->packageWithOption();

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1, 'choices' => [$gold->id]]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1, 'choices' => [$diamond->id]]);

        $this->assertDatabaseCount('store_cart_items', 2);
    }

    public function test_a_required_option_must_be_answered()
    {
        [$package] = $this->packageWithOption();

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertStatus(422);
    }

    public function test_a_choice_from_a_different_package_is_rejected()
    {
        [$package] = $this->packageWithOption();
        [, $foreignChoice] = $this->packageWithOption();

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'choices' => [$foreignChoice->id],
        ])->assertStatus(422);
    }

    public function test_a_disabled_choice_is_rejected()
    {
        [$package, $gold] = $this->packageWithOption();
        $gold->update(['is_enabled' => false]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'choices' => [$gold->id],
        ])->assertStatus(422);
    }

    public function test_quantity_is_clamped_to_the_package_limits()
    {
        $package = StorePackage::factory()->create(['min_quantity' => 2, 'max_quantity' => 5]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 99]);

        $this->assertDatabaseHas('store_cart_items', ['quantity' => 5]);
    }

    public function test_a_disabled_package_cannot_be_added()
    {
        $package = StorePackage::factory()->disabled()->create();

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertStatus(404);
    }

    public function test_an_out_of_stock_package_cannot_be_added()
    {
        $package = StorePackage::factory()->create(['stock_limit' => 1, 'sold_count' => 1]);

        $this->from(route('store.index'))
            ->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_a_members_only_package_redirects_a_guest_to_login()
    {
        $package = StorePackage::factory()->create(['requires_login' => true]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_the_cart_is_capped_at_the_configured_item_count()
    {
        config(['store.cart_max_items' => 2]);

        foreach (range(1, 3) as $i) {
            $package = StorePackage::factory()->create();
            $response = $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

            if ($i === 3) {
                $response->assertStatus(422);
            }
        }

        $this->assertDatabaseCount('store_cart_items', 2);
    }

    public function test_a_cart_line_can_be_updated_and_removed()
    {
        $package = StorePackage::factory()->create();
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $item = StoreCart::first()->items->first();

        $this->patch(route('store.cart.update', $item->id), ['quantity' => 4]);
        $this->assertDatabaseHas('store_cart_items', ['id' => $item->id, 'quantity' => 4]);

        $this->delete(route('store.cart.delete', $item->id));
        $this->assertDatabaseMissing('store_cart_items', ['id' => $item->id]);
    }

    public function test_setting_a_quantity_of_zero_removes_the_line()
    {
        $package = StorePackage::factory()->create();
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $item = StoreCart::first()->items->first();

        $this->patch(route('store.cart.update', $item->id), ['quantity' => 0]);

        $this->assertDatabaseMissing('store_cart_items', ['id' => $item->id]);
    }

    public function test_a_user_cannot_touch_another_visitors_cart_line()
    {
        $package = StorePackage::factory()->create();
        $otherCart = StoreCart::create(['session_token' => 'someone-elses-token']);
        $otherItem = $otherCart->items()->create([
            'store_package_id' => $package->id, 'quantity' => 1, 'options_signature' => md5(''),
        ]);

        $this->actingAs(User::factory()->create())
            ->patch(route('store.cart.update', $otherItem->id), ['quantity' => 99])
            ->assertStatus(403);

        $this->assertDatabaseHas('store_cart_items', ['id' => $otherItem->id, 'quantity' => 1]);
    }

    public function test_the_cart_page_prices_live_rather_than_from_stored_values()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 2000));

        // An admin re-prices the package; the cart must reflect it immediately.
        $package->update(['price' => 1500]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 3000));
    }

    public function test_a_coupon_code_can_be_applied_and_cleared()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        StoreCoupon::create([
            'code' => 'SAVE50', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 5000,
            'is_enabled' => true, 'used_count' => 0,
        ]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->post(route('store.cart.code'), ['code' => 'save50']);
        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.coupon_discount', 1000)->where('quote.total', 1000));

        $this->post(route('store.cart.code'), ['code' => '']);
        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.coupon_discount', 0));
    }

    public function test_a_gift_card_code_can_be_applied()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        StoreGiftCard::create([
            'code' => 'GIFT100', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
        ]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->post(route('store.cart.code'), ['code' => 'GIFT100']);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.gift_card_amount', 500)->where('quote.amount_due', 1500));
    }

    public function test_an_unknown_code_is_reported_without_changing_the_cart()
    {
        $package = StorePackage::factory()->create(['price' => 2000]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->post(route('store.cart.code'), ['code' => 'NOPE']);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 2000));
    }

    public function test_a_guest_cart_merges_into_the_user_cart_on_login()
    {
        $package = StorePackage::factory()->create(['price' => 1000]);
        $user = User::factory()->create();

        // The user already has something in their account cart.
        $userCart = StoreCart::create(['user_id' => $user->id]);
        $userCart->items()->create([
            'store_package_id' => $package->id, 'quantity' => 1, 'options_signature' => md5(''),
        ]);

        $guestCart = StoreCart::create(['session_token' => 'guest-token']);
        $guestCart->items()->create([
            'store_package_id' => $package->id, 'quantity' => 2, 'options_signature' => md5(''),
        ]);

        app(StoreCartService::class)->mergeGuestCartInto($user, 'guest-token');

        // Same package and options, so the quantities sum onto one line.
        $this->assertDatabaseCount('store_cart_items', 1);
        $this->assertDatabaseHas('store_cart_items', ['store_cart_id' => $userCart->id, 'quantity' => 3]);
        $this->assertDatabaseMissing('store_carts', ['id' => $guestCart->id]);
    }

    public function test_merging_keeps_distinct_configurations_as_separate_lines()
    {
        [$package, $gold, $diamond] = $this->packageWithOption();
        $user = User::factory()->create();

        $userCart = StoreCart::create(['user_id' => $user->id]);
        $userCart->items()->create([
            'store_package_id' => $package->id, 'quantity' => 1,
            'selected_options' => [$gold->id], 'options_signature' => md5((string) $gold->id),
        ]);

        $guestCart = StoreCart::create(['session_token' => 'guest-token']);
        $guestCart->items()->create([
            'store_package_id' => $package->id, 'quantity' => 1,
            'selected_options' => [$diamond->id], 'options_signature' => md5((string) $diamond->id),
        ]);

        app(StoreCartService::class)->mergeGuestCartInto($user, 'guest-token');

        $this->assertDatabaseCount('store_cart_items', 2);
    }

    public function test_a_stale_choice_that_was_since_disabled_is_dropped_from_pricing()
    {
        [$package, $gold, $diamond] = $this->packageWithOption();
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1, 'choices' => [$diamond->id]]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 1500));

        // The admin withdraws the choice; the cart must fall back rather than keep charging for it.
        $diamond->update(['is_enabled' => false]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 1000));
    }

    public function test_the_cart_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $this->get(route('store.cart.show'))->assertStatus(403);
    }
}

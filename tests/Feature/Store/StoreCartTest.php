<?php

use App\Enums\StoreDiscountType;
use App\Models\StoreCart;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // A real browser returns the cart cookie on every request; the test client does not carry
    // queued cookies forward, so it is pinned here for the whole test. withCookie (not
    // withUnencryptedCookie) so the value is encrypted the way EncryptCookies expects.
    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');
});

test('a guest can add a package to a cart', function () {
    $package = StorePackage::factory()->create(['price' => 999]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2])
        ->assertRedirect(route('store.cart.show'));

    $this->assertDatabaseCount('store_carts', 1);
    $this->assertDatabaseHas('store_cart_items', ['store_package_id' => $package->id, 'quantity' => 2]);
});

test('a guest cart is keyed on a cookie token', function () {
    $package = StorePackage::factory()->create();

    $response = $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $response->assertCookie(StoreCartService::COOKIE);
    expect(StoreCart::first()->session_token)->not->toBeNull();
    expect(StoreCart::first()->user_id)->toBeNull();
});

test('a logged in user gets exactly one cart', function () {
    $user = User::factory()->create();
    $package = StorePackage::factory()->create();

    $this->actingAs($user)->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    $this->actingAs($user)->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->assertDatabaseCount('store_carts', 1);
    expect(StoreCart::first()->user_id)->toEqual($user->id);
});

test('adding the same package twice merges into one line', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2]);

    $this->assertDatabaseCount('store_cart_items', 1);
    $this->assertDatabaseHas('store_cart_items', ['quantity' => 3]);
});

test('quantity is clamped to the package limits', function () {
    $package = StorePackage::factory()->create(['min_quantity' => 2, 'max_quantity' => 5]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 99]);

    $this->assertDatabaseHas('store_cart_items', ['quantity' => 5]);
});

test('a disabled package cannot be added', function () {
    $package = StorePackage::factory()->disabled()->create();

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertStatus(404);
});

test('an out of stock package cannot be added', function () {
    $package = StorePackage::factory()->create(['global_purchase_limit' => 1, 'sold_count' => 1]);

    $this->from(route('store.index'))
        ->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('a members only package redirects a guest to login', function () {
    $package = StorePackage::factory()->create(['requires_login' => true]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('the cart is capped at the configured item count', function () {
    config(['store.cart_max_items' => 2]);

    foreach (range(1, 3) as $i) {
        $package = StorePackage::factory()->create();
        $response = $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        if ($i === 3) {
            $response->assertStatus(422);
        }
    }

    $this->assertDatabaseCount('store_cart_items', 2);
});

test('a cart line can be updated and removed', function () {
    $package = StorePackage::factory()->create();
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    $item = StoreCart::first()->items->first();

    $this->patch(route('store.cart.update', $item->id), ['quantity' => 4]);
    $this->assertDatabaseHas('store_cart_items', ['id' => $item->id, 'quantity' => 4]);

    $this->delete(route('store.cart.delete', $item->id));
    $this->assertDatabaseMissing('store_cart_items', ['id' => $item->id]);
});

test('setting a quantity of zero removes the line', function () {
    $package = StorePackage::factory()->create();
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    $item = StoreCart::first()->items->first();

    $this->patch(route('store.cart.update', $item->id), ['quantity' => 0]);

    $this->assertDatabaseMissing('store_cart_items', ['id' => $item->id]);
});

test('a user cannot touch another visitors cart line', function () {
    $package = StorePackage::factory()->create();
    $otherCart = StoreCart::create(['session_token' => 'someone-elses-token']);
    $otherItem = $otherCart->items()->create([
        'store_package_id' => $package->id, 'quantity' => 1,
    ]);

    $this->actingAs(User::factory()->create())
        ->patch(route('store.cart.update', $otherItem->id), ['quantity' => 99])
        ->assertStatus(403);

    $this->assertDatabaseHas('store_cart_items', ['id' => $otherItem->id, 'quantity' => 1]);
});

test('the cart page prices live rather than from stored values', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 2]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.total', 2000));

    // An admin re-prices the package; the cart must reflect it immediately.
    $package->update(['price' => 1500]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.total', 3000));
});

test('a coupon code can be applied and cleared', function () {
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
});

test('a gift card code can be applied', function () {
    $package = StorePackage::factory()->create(['price' => 2000]);
    StoreGiftCard::create([
        'code' => 'GIFT100', 'currency_code' => 'USD', 'original_balance' => 500, 'balance' => 500, 'is_enabled' => true,
    ]);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->post(route('store.cart.code'), ['code' => 'GIFT100']);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.gift_card_amount', 500)->where('quote.amount_due', 1500));
});

test('an unknown code is reported without changing the cart', function () {
    $package = StorePackage::factory()->create(['price' => 2000]);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->post(route('store.cart.code'), ['code' => 'NOPE']);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.total', 2000));
});

test('a guest cart merges into the user cart on login', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $user = User::factory()->create();

    // The user already has something in their account cart.
    $userCart = StoreCart::create(['user_id' => $user->id]);
    $userCart->items()->create([
        'store_package_id' => $package->id, 'quantity' => 1,
    ]);

    $guestCart = StoreCart::create(['session_token' => 'guest-token']);
    $guestCart->items()->create([
        'store_package_id' => $package->id, 'quantity' => 2,
    ]);

    app(StoreCartService::class)->mergeGuestCartInto($user, 'guest-token');

    // Same package, so the quantities sum onto one line.
    $this->assertDatabaseCount('store_cart_items', 1);
    $this->assertDatabaseHas('store_cart_items', ['store_cart_id' => $userCart->id, 'quantity' => 3]);
    $this->assertDatabaseMissing('store_carts', ['id' => $guestCart->id]);
});

test('the cart is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $this->get(route('store.cart.show'))->assertStatus(403);
});

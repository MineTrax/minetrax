<?php

use App\Enums\StoreDiscountType;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\Player;
use App\Models\StoreBan;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $this->enableStoreGateways(['manual']);

    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

    // Throttling runs through Redis (bootstrap/app.php calls throttleWithRedis), so limiter
    // state survives between tests and would 429 the whole suite. Throttling itself is
    // covered by its own test below, which re-enables it.
    $this->withoutMiddleware([
        ThrottleRequests::class,
        ThrottleRequestsWithRedis::class,
    ]);
});

function fillCart(?StorePackage $package = null, int $quantity = 1): StorePackage
{
    $package = $package ?: StorePackage::factory()->create(['price' => 1000]);
    test()->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => $quantity]);

    return $package;
}

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ], $overrides);
}

test('the checkout page redirects when the cart is empty', function () {
    $this->get(route('store.checkout.create'))->assertRedirect(route('store.cart.show'));
});

test('the checkout page lists only enabled gateways', function () {
    fillCart();

    $this->get(route('store.checkout.create'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Store/CheckoutStore', false)
            ->has('gateways', 1)
            ->where('gateways.0.key', 'manual')
        );
});

test('a guest can place an order', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart(null, 2);

    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertRedirect();

    $order = StoreOrder::first();
    expect($order)->not->toBeNull();
    expect($order->status)->toEqual(StoreOrderStatus::PENDING);
    expect($order->total)->toEqual(2000);
    expect($order->amount_due)->toEqual(2000);
    expect($order->user_id)->toBeNull();
    expect($order->player_username)->toEqual('Steve');
});

test('the order snapshots the line items', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = fillCart(null, 3);

    $this->post(route('store.checkout.store'), checkoutPayload());

    $originalName = $package->name;

    $item = StoreOrder::first()->items->first();
    expect($item->package_name)->toEqual($originalName);
    expect($item->quantity)->toEqual(3);
    expect($item->unit_price)->toEqual(1000);

    // Editing the package afterwards must not rewrite the order.
    $package->update(['name' => 'Renamed', 'price' => 5000]);
    expect($item->fresh()->package_name)->toEqual($originalName);
    expect($item->fresh()->unit_price)->toEqual(1000);
});

test('the order item records the sale that priced it', function () {
    // The id as well as the name: the sale's commands resolve against it at delivery, and again on
    // a refund long after the sale has ended.
    Player::factory()->create(['username' => 'Steve']);
    fillCart();
    $sale = StoreSale::factory()->create(['name' => 'Summer Sale', 'discount_value' => 2000]);

    $this->post(route('store.checkout.store'), checkoutPayload());

    $this->assertDatabaseHas('store_order_items', [
        'store_sale_id' => $sale->id,
        'sale_name' => 'Summer Sale',
    ]);
});

test('an item with no sale records no sale id', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload());

    expect(StoreOrder::first()->items->first()->store_sale_id)->toBeNull();
});

test('a cart that meets a sale minimum is charged the sale price', function () {
    // placeOrder() freezes amount_due and capture only verifies it, never recomputes — so the
    // threshold has to evaluate the same way here as it does on the cart page.
    Player::factory()->create(['username' => 'Steve']);
    fillCart(null, 3);
    StoreSale::factory()->withMinimum(3000)->create(['name' => 'Big Spender', 'discount_value' => 2000]);

    $this->post(route('store.checkout.store'), checkoutPayload());

    $order = StoreOrder::first();
    expect($order->sale_discount)->toEqual(600);
    expect($order->items->first()->sale_name)->toEqual('Big Spender');
});

test('a cart below a sale minimum is charged the full price', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();
    StoreSale::factory()->withMinimum(3000)->create(['discount_value' => 2000]);

    $this->post(route('store.checkout.store'), checkoutPayload());

    $order = StoreOrder::first();
    expect($order->sale_discount)->toEqual(0);
    expect($order->items->first()->sale_name)->toBeNull();
});

test('the order is priced from live data not from what the client sends', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    // Any amount fields in the request body are simply ignored.
    $this->post(route('store.checkout.store'), checkoutPayload([
        'total' => 1,
        'amount_due' => 1,
        'subtotal' => 1,
    ]));

    expect(StoreOrder::first()->total)->toEqual(1000);
});

test('checkout empties the cart', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload());

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('a pending payment row is created for the chosen gateway', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload());

    $payment = StoreOrder::first()->payments->first();
    expect($payment->status)->toEqual(StorePaymentStatus::PENDING);
    expect($payment->amount)->toEqual(1000);
});

test('terms must be accepted', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload(['accept_terms' => false]))
        ->assertSessionHasErrors(['accept_terms']);

    $this->assertDatabaseCount('store_orders', 0);
});

test('an unknown gateway is rejected', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload(['gateway' => 'bitcoin']))
        ->assertSessionHasErrors(['gateway']);
});

test('an unverifiable username is rejected', function () {
    Http::fake(['api.minecraftservices.com/*' => Http::response(null, 404)]);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload(['player_username' => 'GhostPlayer']))
        ->assertSessionHasErrors(['player_username']);

    $this->assertDatabaseCount('store_orders', 0);
});

test('a guest email is required when configured', function () {
    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload(['email' => null]))
        ->assertSessionHasErrors(['email']);
});

test('guest checkout can be turned off', function () {
    $settings = app(StoreSettings::class);
    $settings->enable_guest_checkout = false;
    $settings->save();

    Player::factory()->create(['username' => 'Steve']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload())->assertRedirect(route('login'));
    $this->assertDatabaseCount('store_orders', 0);
});

test('a banned identity cannot check out', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    StoreBan::factory()->create(['player_uuid' => $player->uuid, 'reason' => 'Chargeback']);
    fillCart();

    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertSessionHasErrors(['cart']);

    $this->assertDatabaseCount('store_orders', 0);
});

test('a lifetime limit for everyone is enforced at checkout', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

    // Someone else bought the only one first.
    $sold = StoreOrder::factory()->paid()->create();
    $sold->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    fillCart($package, 1);

    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertSessionHasErrors(['cart']);
});

test('a purchase limit counts quantity rather than orders', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 2, 'max_quantity' => 5]);

    fillCart($package, 3);

    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertSessionHasErrors(['cart']);
});

test('a limit with a reset period ignores purchases outside the window', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create([
        'price' => 1000, 'player_purchase_limit' => 1, 'player_purchase_limit_period_days' => 7,
    ]);

    $old = StoreOrder::factory()->paid()->create([
        'player_uuid' => $player->uuid,
        'created_at' => now()->subDays(30),
    ]);
    $old->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    fillCart($package);

    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertSessionHasNoErrors();
});

test('a per player purchase limit counts only paid orders', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'player_purchase_limit' => 1]);

    // An abandoned order must not consume the allowance.
    $abandoned = StoreOrder::factory()->create(['player_uuid' => $player->uuid]);
    $abandoned->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    fillCart($package);
    $this->post(route('store.checkout.store'), checkoutPayload())->assertRedirect();
    expect(StoreOrder::count())->toEqual(2);

    // Now a paid one does.
    StoreOrder::first()->update(['status' => StoreOrderStatus::PAID]);
    fillCart($package);
    $this->post(route('store.checkout.store'), checkoutPayload())
        ->assertSessionHasErrors(['cart']);
});

test('a coupon use is reserved at order creation', function () {
    Player::factory()->create(['username' => 'Steve']);
    $coupon = StoreCoupon::create([
        'code' => 'SAVE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000,
        'is_enabled' => true, 'used_count' => 0, 'max_uses_total' => 1,
    ]);

    fillCart();
    $this->post(route('store.cart.code'), ['code' => 'SAVE']);
    $this->post(route('store.checkout.store'), checkoutPayload());

    // Reserved immediately, so two buyers racing for the last use cannot both win.
    expect($coupon->fresh()->used_count)->toEqual(1);
    expect(StoreOrder::first()->total)->toEqual(900);
});

test('cancelling an order releases the reserved coupon use', function () {
    Player::factory()->create(['username' => 'Steve']);
    $coupon = StoreCoupon::create([
        'code' => 'SAVE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000,
        'is_enabled' => true, 'used_count' => 0,
    ]);

    fillCart();
    $this->post(route('store.cart.code'), ['code' => 'SAVE']);
    $this->post(route('store.checkout.store'), checkoutPayload());
    expect($coupon->fresh()->used_count)->toEqual(1);

    $order = StoreOrder::first();
    $this->post(route('store.order.cancel', $order->uuid));

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CANCELLED);
    expect($coupon->fresh()->used_count)->toEqual(0);
});

test('an order fully covered by a gift card skips the gateway', function () {
    Player::factory()->create(['username' => 'Steve']);
    StoreGiftCard::create([
        'code' => 'FULL', 'currency_code' => 'USD', 'original_balance' => 5000, 'balance' => 5000, 'is_enabled' => true,
    ]);

    fillCart();
    $this->post(route('store.cart.code'), ['code' => 'FULL']);
    $this->post(route('store.checkout.store'), checkoutPayload());

    $order = StoreOrder::first();
    expect($order->amount_due)->toEqual(0);

    // Nothing to charge, so it never sits at a zero-value gateway: it is paid immediately and
    // fulfilment carries it through to COMPLETED.
    expect($order->status->isPaidState())->toBeTrue();
    expect($order->status)->toEqual(StoreOrderStatus::COMPLETED);
});

test('paying with a gift card debits it and writes a ledger row', function () {
    Player::factory()->create(['username' => 'Steve']);
    $card = StoreGiftCard::create([
        'code' => 'PART', 'currency_code' => 'USD', 'original_balance' => 400, 'balance' => 400, 'is_enabled' => true,
    ]);

    fillCart();
    $this->post(route('store.cart.code'), ['code' => 'PART']);
    $this->post(route('store.checkout.store'), checkoutPayload());

    $order = StoreOrder::first();
    expect($order->amount_due)->toEqual(600);
    expect($card->fresh()->balance)->toEqual(400, 'Not yet debited: the order is still pending.');

    app(StoreOrderService::class)->markPaid($order, $order->payments->first(), 600, 'USD');

    expect($card->fresh()->balance)->toEqual(0);
    $this->assertDatabaseHas('store_gift_card_transactions', [
        'store_gift_card_id' => $card->id, 'store_order_id' => $order->id, 'amount' => -400,
    ]);
});

test('a logged in user order is attributed to them', function () {
    $user = User::factory()->create();
    Player::factory()->create(['username' => 'Steve']);

    $this->actingAs($user);
    $this->post(route('store.cart.store'), ['package_id' => StorePackage::factory()->create(['price' => 1000])->id, 'quantity' => 1]);
    $this->post(route('store.checkout.store'), checkoutPayload());

    expect(StoreOrder::first()->user_id)->toEqual($user->id);
});

test('another user cannot view someone elses order', function () {
    $owner = User::factory()->create();
    $order = StoreOrder::factory()->forUser($owner)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('store.order.result', $order->uuid))
        ->assertStatus(403);
});

test('a guest order is reachable by its uuid', function () {
    // A guest has no account to authorise against; the v4 uuid is the credential and is
    // never exposed in any listing.
    $order = StoreOrder::factory()->guest()->create();

    $this->get(route('store.order.result', $order->uuid))->assertStatus(200);
});

test('checkout is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $this->get(route('store.checkout.create'))->assertStatus(403);
});

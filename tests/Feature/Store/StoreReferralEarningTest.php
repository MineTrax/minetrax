<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\Country;
use App\Models\Player;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\StoreTax;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreOrderService;
use App\Services\StoreReferralService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
    $this->enableStoreGateways(['manual']);

    // A fixed guest cart token, so the add-to-cart and the checkout are the same visitor.
    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

    $this->withoutMiddleware([ThrottleRequests::class, ThrottleRequestsWithRedis::class]);
});

function earningCart(int $price = 1000, int $quantity = 1): StorePackage
{
    $package = StorePackage::factory()->create(['price' => $price]);
    test()->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => $quantity]);

    return $package;
}

function earningCheckout(array $overrides = []): array
{
    return array_merge([
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ], $overrides);
}

/**
 * Take the order all the way to PAID through the real service, so the earning is written by the
 * same path a gateway webhook uses.
 */
function earningMarkPaid(StoreOrder $order): bool
{
    $payment = $order->payments()->first();

    return app(StoreOrderService::class)
        ->markPaid($order, $payment, (int) $order->amount_due, $order->currency);
}

test('checkout snapshots the referral, its code and its rate', function () {
    Player::factory()->create(['username' => 'Steve']);
    $referral = StoreReferral::factory()->share(500)->create(['code' => 'KAKAMORA']);
    earningCart();

    $this->withCookie(StoreReferralService::COOKIE, 'KAKAMORA')
        ->post(route('store.checkout.store'), earningCheckout())
        ->assertSessionHasNoErrors();

    $order = StoreOrder::first();

    expect($order->store_referral_id)->toBe($referral->id);
    expect($order->referral_code)->toBe('KAKAMORA');
    expect($order->referral_share_bp)->toBe(500);
    expect($order->referral_source)->toBe(StoreReferralService::SOURCE_URL);

    // Nothing is owed until the money arrives.
    expect($order->referral_earning)->toBeNull();
});

test('a typed code is recorded as such', function () {
    Player::factory()->create(['username' => 'Steve']);
    StoreReferral::factory()->create(['code' => 'TYPED']);
    earningCart();
    $this->post(route('store.cart.referral.store'), ['code' => 'TYPED']);

    $this->post(route('store.checkout.store'), earningCheckout())->assertSessionHasNoErrors();

    expect(StoreOrder::first()->referral_source)->toBe(StoreReferralService::SOURCE_MANUAL);
});

test('the earning is the share of the total less tax', function () {
    // Tax is the government's money, not revenue. Paying a commission on it would have the store
    // owe more than it kept.
    // The tax rule is chosen by the country the buyer declares, so the address has to be collected
    // for one to apply at all.
    $settings = app(StoreSettings::class);
    $settings->collect_billing_address = true;
    $settings->save();

    $country = Country::first();
    StoreTax::create([
        'name' => 'Overworld VAT',
        'country_id' => $country->id,
        'rate_bp' => 2000,
        'is_inclusive' => false,
        'is_enabled' => true,
    ]);

    Player::factory()->create(['username' => 'Steve']);
    StoreReferral::factory()->share(500)->create(['code' => 'KAKAMORA']);
    earningCart(1000);

    $this->withCookie(StoreReferralService::COOKIE, 'KAKAMORA')
        ->post(route('store.checkout.store'), earningCheckout([
            'billing_name' => 'Steve',
            'billing_address_line1' => '1 Cobble Way',
            'billing_city' => 'Overworld',
            'billing_postal_code' => '00001',
            'billing_country_id' => $country->id,
        ]))->assertSessionHasNoErrors();

    $order = StoreOrder::first();
    earningMarkPaid($order);
    $order->refresh();

    // 1000 goods + 200 tax = 1200 total. 5% of the 1000, not of the 1200.
    expect($order->total)->toBe(1200);
    expect($order->tax_amount)->toBe(200);
    expect($order->referral_earning)->toBe(50);
});

test('the base figure converts through the order own rate, not today rate', function () {
    // Re-converting at report time would silently rewrite history every time a rate moved.
    StoreCurrency::create([
        'code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'symbol_position' => 'prefix',
        'exponent' => 0, 'rate_to_base' => 0.0067, 'is_base' => false, 'is_enabled' => true,
        'price_rounding' => 'none', 'sort_order' => 2,
    ]);

    $referral = StoreReferral::factory()->share(1000)->create();

    // ¥3000 of goods, snapshotted at $20.10 in the base currency.
    $order = StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'referral_share_bp' => 1000,
        'currency' => 'JPY',
        'total' => 3000,
        'tax_amount' => 0,
        'amount_due' => 3000,
        'base_total' => 2010,
    ]);
    $payment = $order->payments()->create([
        'gateway' => 'manual', 'status' => StorePaymentStatus::PENDING,
        'amount' => 3000, 'currency' => 'JPY',
    ]);

    app(StoreOrderService::class)->markPaid($order, $payment, 3000, 'JPY');
    $order->refresh();

    // A zero-decimal currency: ¥300 is 300 minor units, never 30000.
    expect($order->referral_earning)->toBe(300);
    // 10% of the order's own base_total, arrived at by ratio rather than by re-converting.
    expect($order->referral_earning_base)->toBe(201);
});

test('marking an order paid twice does not pay the referrer twice', function () {
    Player::factory()->create(['username' => 'Steve']);
    StoreReferral::factory()->share(500)->create(['code' => 'KAKAMORA']);
    earningCart(1000);

    $this->withCookie(StoreReferralService::COOKIE, 'KAKAMORA')
        ->post(route('store.checkout.store'), earningCheckout())->assertSessionHasNoErrors();

    $order = StoreOrder::first();

    expect(earningMarkPaid($order))->toBeTrue();
    expect($order->fresh()->referral_earning)->toBe(50);

    // The replay is refused by the state machine before it reaches the earning at all.
    expect(earningMarkPaid($order->fresh()))->toBeFalse();
    expect($order->fresh()->referral_earning)->toBe(50);

    $referral = StoreReferral::first();
    expect($referral->earnedBase())->toBe(50);
});

test('an order with no referral records no earning', function () {
    Player::factory()->create(['username' => 'Steve']);
    earningCart(1000);

    $this->post(route('store.checkout.store'), earningCheckout())->assertSessionHasNoErrors();

    $order = StoreOrder::first();
    earningMarkPaid($order);

    expect($order->fresh()->referral_earning)->toBeNull();
});

test('a partial refund scales the earning, and a second scales from the snapshots', function () {
    // Recalculated from referral_share_bp and the frozen totals each time rather than adjusted in
    // place, so several partials cannot drift apart from the truth.
    $referral = StoreReferral::factory()->share(1000)->create();
    $order = StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_share_bp' => 1000,
        'total' => 1000, 'tax_amount' => 0, 'amount_due' => 1000, 'base_total' => 1000,
    ]);
    $payment = $order->payments()->create([
        'gateway' => 'manual', 'status' => StorePaymentStatus::COMPLETED,
        'amount' => 1000, 'currency' => 'USD',
    ]);

    $orders = app(StoreOrderService::class);

    $orders->recordRefund($payment->fresh(), 250);
    expect($order->fresh()->referral_earning)->toBe(75);   // 10% of the 750 still standing
    expect($order->fresh()->status)->toBe(StoreOrderStatus::PARTIALLY_REFUNDED);

    $orders->recordRefund($payment->fresh(), 250);
    expect($order->fresh()->referral_earning)->toBe(50);   // 10% of 500, not 10% of 75
});

test('a fully refunded or charged back order leaves the balance entirely', function () {
    $referral = StoreReferral::factory()->share(1000)->create();

    foreach ([false, true] as $isChargeback) {
        $order = StoreOrder::factory()->paid()->create([
            'store_referral_id' => $referral->id,
            'referral_share_bp' => 1000,
            'total' => 1000, 'tax_amount' => 0, 'amount_due' => 1000, 'base_total' => 1000,
        ]);
        $payment = $order->payments()->create([
            'gateway' => 'manual', 'status' => StorePaymentStatus::COMPLETED,
            'amount' => 1000, 'currency' => 'USD',
        ]);

        app(StoreOrderService::class)->recordRefund($payment->fresh(), 1000, $isChargeback);

        expect($order->fresh()->referral_earning)->toBe(0);
        expect($order->fresh()->status->isPaidState())->toBeFalse();
    }

    expect($referral->fresh()->earnedBase())->toBe(0);
});

test('a chargeback owes nothing even when the gateway names a smaller figure', function () {
    // A dispute reverses the payment whatever amount the notification carries.
    $referral = StoreReferral::factory()->share(1000)->create();
    $order = StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_share_bp' => 1000,
        'total' => 1000, 'tax_amount' => 0, 'amount_due' => 1000, 'base_total' => 1000,
    ]);
    $payment = $order->payments()->create([
        'gateway' => 'manual', 'status' => StorePaymentStatus::COMPLETED,
        'amount' => 1000, 'currency' => 'USD',
    ]);

    app(StoreOrderService::class)->recordRefund($payment->fresh(), 100, true);

    expect($order->fresh()->referral_earning)->toBe(0);
});

test('an order paid entirely by gift card still earns its referrer', function () {
    // amount_due is zero, so there is nothing refundable at the gateway — but a sale happened and
    // the referrer brought it in. Dividing by amount_due here would be a divide by zero.
    $referral = StoreReferral::factory()->share(1000)->create();
    // Pending, not paid: markPaid() is the transition under test, and the state machine turns away
    // an order that has already made it.
    $order = StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'referral_share_bp' => 1000,
        'total' => 1000, 'tax_amount' => 0, 'gift_card_amount' => 1000,
        'amount_due' => 0, 'base_total' => 1000,
    ]);
    $payment = $order->payments()->create([
        'gateway' => 'manual', 'status' => StorePaymentStatus::COMPLETED,
        'amount' => 0, 'currency' => 'USD',
    ]);

    app(StoreOrderService::class)->markPaid($order->fresh(), $payment, 0, 'USD');

    expect($order->fresh()->referral_earning)->toBe(100);
});

test('a member earns nothing on their own order', function () {
    $member = User::factory()->create();
    Player::factory()->create(['username' => 'Steve']);
    StoreReferral::factory()->forUser($member)->share(500)->create(['code' => 'SELFIE']);

    $this->actingAs($member);
    earningCart(1000);

    $this->withCookie(StoreReferralService::COOKIE, 'SELFIE')
        ->post(route('store.checkout.store'), earningCheckout())->assertSessionHasNoErrors();

    $order = StoreOrder::first();
    earningMarkPaid($order);

    expect($order->fresh()->store_referral_id)->toBeNull();
    expect($order->fresh()->referral_earning)->toBeNull();
});

test('a disabled referral stops earning on new orders', function () {
    Player::factory()->create(['username' => 'Steve']);
    StoreReferral::factory()->disabled()->create(['code' => 'RETIRED']);
    earningCart(1000);

    $this->withCookie(StoreReferralService::COOKIE, 'RETIRED')
        ->post(route('store.checkout.store'), earningCheckout())->assertSessionHasNoErrors();

    expect(StoreOrder::first()->store_referral_id)->toBeNull();
});

test('changing a referral share does not re-price what it already earned', function () {
    // The rate is snapshotted on the order, so a later edit cannot reach backwards.
    $referral = StoreReferral::factory()->share(500)->create();
    $order = StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_share_bp' => 500,
        'total' => 1000, 'tax_amount' => 0, 'amount_due' => 1000, 'base_total' => 1000,
    ]);
    $payment = $order->payments()->create([
        'gateway' => 'manual', 'status' => StorePaymentStatus::COMPLETED,
        'amount' => 1000, 'currency' => 'USD',
    ]);

    $referral->update(['share_bp' => 9000]);

    app(StoreOrderService::class)->recordRefund($payment->fresh(), 500);

    // 5% of the 500 still standing, not 90% of it.
    expect($order->fresh()->referral_earning)->toBe(25);
});

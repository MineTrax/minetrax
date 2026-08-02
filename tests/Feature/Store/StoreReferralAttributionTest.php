<?php

use App\Enums\StoreReferralAttributionMode;
use App\Models\StoreCart;
use App\Models\StoreCoupon;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

/**
 * The referral cookie as the browser would send it back.
 *
 * Plain text on purpose: withCookies() encrypts it *and* adds the value prefix Laravel checks on
 * the way in, which hand-encrypting would leave off — and a cookie without it is discarded as
 * tampered-with, so the app would silently see no referral at all.
 *
 * @return array<string, string>
 */
function refCookie(string $code): array
{
    return [StoreReferralService::COOKIE => $code];
}

/**
 * Put a package in a guest's cart and hand back every cookie needed to find it again.
 *
 * A guest cart is keyed by an opaque token cookie that the add-to-cart response mints. Without
 * carrying it forward the next request looks like a different visitor with an empty basket, and the
 * assertions below would pass or fail for reasons that have nothing to do with referrals.
 *
 * @param  array<string, string>  $cookies
 * @return array<string, string>
 */
function refGuestCart(array $cookies, StorePackage $package): array
{
    $response = test()->withCookies($cookies)
        ->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    return $cookies + [
        StoreCartService::COOKIE => $response->getCookie(StoreCartService::COOKIE)->getValue(),
    ];
}

/**
 * The code the response leaves the browser holding, or null for "unchanged or forgotten".
 */
function refCookieValue(TestResponse $response): ?string
{
    // Nothing was queued, so whatever the browser already had simply stays.
    if (! $response->getCookie(StoreReferralService::COOKIE, false)) {
        return null;
    }

    // Decrypting also strips the prefix. A forgotten cookie decrypts to an empty string.
    $value = $response->getCookie(StoreReferralService::COOKIE)->getValue();

    return $value === '' ? null : $value;
}

test('a ref link stores the code and counts the visit', function () {
    $referral = StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    $response = $this->get(route('store.index', ['ref' => 'KAKAMORA']));

    expect(refCookieValue($response))->toBe('KAKAMORA');
    expect($referral->fresh()->visit_count)->toBe(1);
    expect($referral->fresh()->last_visited_at)->not->toBeNull();
});

test('a lowercase ref still lands, because buyers copy these by hand', function () {
    StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    expect(refCookieValue($this->get(route('store.index', ['ref' => 'kakamora']))))->toBe('KAKAMORA');
});

test('a disabled, trashed or untracked code stores nothing', function () {
    $disabled = StoreReferral::factory()->disabled()->create(['code' => 'OFF']);
    $untracked = StoreReferral::factory()->withoutUrlTracking()->create(['code' => 'TYPEDONLY']);
    $trashed = StoreReferral::factory()->create(['code' => 'GONE']);
    $trashed->delete();

    foreach (['OFF', 'TYPEDONLY', 'GONE', 'NEVEREXISTED'] as $code) {
        expect(refCookieValue($this->get(route('store.index', ['ref' => $code]))))
            ->toBeNull("[{$code}] should not have been stored.");
    }

    expect($disabled->fresh()->visit_count)->toBe(0);
    expect($untracked->fresh()->visit_count)->toBe(0);
});

test('first touch leaves an existing code alone', function () {
    StoreReferral::factory()->create(['code' => 'FIRST']);
    StoreReferral::factory()->mode(StoreReferralAttributionMode::FIRST_TOUCH)->create(['code' => 'SECOND']);

    $response = $this->withCookies(refCookie('FIRST'))->get(route('store.index', ['ref' => 'SECOND']));

    // No cookie is queued at all, so whatever the browser holds simply stays.
    expect(refCookieValue($response))->toBeNull();
});

test('last touch takes the credit', function () {
    StoreReferral::factory()->create(['code' => 'FIRST']);
    StoreReferral::factory()->mode(StoreReferralAttributionMode::LAST_TOUCH)->create(['code' => 'SECOND']);

    $response = $this->withCookies(refCookie('FIRST'))->get(route('store.index', ['ref' => 'SECOND']));

    expect(refCookieValue($response))->toBe('SECOND');
});

test('extend window keeps the original code and restarts its clock', function () {
    StoreReferral::factory()->create(['code' => 'FIRST', 'attribution_window_days' => 30]);
    StoreReferral::factory()->mode(StoreReferralAttributionMode::EXTEND_WINDOW)->create(['code' => 'SECOND']);

    $response = $this->withCookies(refCookie('FIRST'))->get(route('store.index', ['ref' => 'SECOND']));

    expect(refCookieValue($response))->toBe('FIRST');

    // The window that matters is the stored code's, not the arriving one's.
    $lifetimeMinutes = ($response->getCookie(StoreReferralService::COOKIE, false)->getExpiresTime() - time()) / 60;
    expect($lifetimeMinutes)->toBeGreaterThan(29 * 24 * 60);
});

test('the same code arriving twice is not treated as a second source', function () {
    // Under first touch this would otherwise be a no-op that looks correct by accident. Re-arriving
    // on your own link must still refresh, not be ignored as a rival.
    $referral = StoreReferral::factory()->mode(StoreReferralAttributionMode::FIRST_TOUCH)->create(['code' => 'SAME']);

    $response = $this->withCookies(refCookie('SAME'))->get(route('store.index', ['ref' => 'SAME']));

    expect(refCookieValue($response))->toBe('SAME');
    expect($referral->fresh()->visit_count)->toBe(1);
});

test('no window means a lifetime cookie', function () {
    StoreReferral::factory()->lifetime()->create(['code' => 'FOREVER']);

    $response = $this->get(route('store.index', ['ref' => 'FOREVER']));
    $years = ($response->getCookie(StoreReferralService::COOKIE, false)->getExpiresTime() - time()) / (60 * 60 * 24 * 365);

    expect($years)->toBeGreaterThan(4);
});

test('a ref is ignored entirely when the store is off', function () {
    config(['store.enabled' => false]);
    $referral = StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    expect(refCookieValue($this->get('/?ref=KAKAMORA')))->toBeNull();
    expect($referral->fresh()->visit_count)->toBe(0);
});

test('the tracked code credits the referrer on the cart', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA', 'referrer_name' => 'Kakamora']);

    $cookies = refGuestCart(refCookie('KAKAMORA'), $package);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.referral.code', 'KAKAMORA')
            ->where('quote.referral.referrer_name', 'Kakamora')
        );
});

test('a tracked referral applies its coupon without writing to the cart', function () {
    // The visitor may have no cart row at all when the link is followed, and looking at a page must
    // never mint one. So the discount is resolved at quote time instead.
    $package = StorePackage::factory()->create(['price' => 1000]);
    $coupon = StoreCoupon::factory()->create(['code' => 'CREATOR10', 'discount_value' => 1000]);
    StoreReferral::factory()->withCoupon($coupon)->create(['code' => 'KAKAMORA']);

    $this->get(route('store.index', ['ref' => 'KAKAMORA']));
    expect(StoreCart::count())->toBe(0, 'Following a link must not create a cart.');

    $cookies = refGuestCart(refCookie('KAKAMORA'), $package);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.coupon_discount', 100));

    // And the cart itself never had the coupon written onto it.
    expect(StoreCart::first()->store_coupon_id)->toBeNull();
});

test('a coupon the buyer typed beats the one a referral hands out', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $referralCoupon = StoreCoupon::factory()->create(['code' => 'CREATOR10', 'discount_value' => 1000]);
    $typedCoupon = StoreCoupon::factory()->create(['code' => 'BIGGER', 'discount_value' => 5000]);
    StoreReferral::factory()->withCoupon($referralCoupon)->create(['code' => 'KAKAMORA']);

    $cookies = refGuestCart(refCookie('KAKAMORA'), $package);
    $this->withCookies($cookies)->post(route('store.cart.code'), ['code' => 'BIGGER']);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.coupon_discount', 500)
            // Still credited, even though their discount lost.
            ->where('quote.referral.code', 'KAKAMORA')
        );
});

test('typing a referral code into the cart credits the referrer', function () {
    // The plainest path there is, and the one a buyer who was handed a code rather than a link
    // actually takes. It shares the box with coupons and gift cards.
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA', 'referrer_name' => 'Kakamora']);

    $cookies = refGuestCart([], $package);
    $this->withCookies($cookies)->post(route('store.cart.referral.store'), ['code' => 'kakamora']);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.referral.referrer_name', 'Kakamora')
            // The box stays free, because a referral does not occupy the coupon slot: the buyer can
            // still add a coupon or a gift card on top.
            ->where('quote.applied_code', null)
        );

    expect(StoreCart::first()->store_referral_id)->not->toBeNull();
});

test('a referral code and a coupon can both be typed in', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreCoupon::factory()->create(['code' => 'SAVE10', 'discount_value' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA', 'referrer_name' => 'Kakamora']);

    $cookies = refGuestCart([], $package);
    $this->withCookies($cookies)->post(route('store.cart.referral.store'), ['code' => 'KAKAMORA']);
    $this->withCookies($cookies)->post(route('store.cart.code'), ['code' => 'SAVE10']);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.referral.code', 'KAKAMORA')
            ->where('quote.coupon_discount', 100)
        );
});

test('a typed code beats a tracked one', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->create(['code' => 'FROMLINK']);
    StoreReferral::factory()->create(['code' => 'TYPED', 'referrer_name' => 'Typed']);

    $cookies = refGuestCart(refCookie('FROMLINK'), $package);
    $this->withCookies($cookies)->post(route('store.cart.referral.store'), ['code' => 'TYPED']);

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.referral.code', 'TYPED'));
});

test('clearing the coupon box leaves the referrer alone', function () {
    // The two have separate fields, so clearing one must not silently take the other with it. This
    // was the behaviour when they shared a box, and it is exactly what the split is for.
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreCoupon::factory()->create(['code' => 'MINE', 'discount_value' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    $cookies = refGuestCart(refCookie('KAKAMORA'), $package);
    $this->withCookies($cookies)->post(route('store.cart.code'), ['code' => 'MINE']);

    $response = $this->withCookies($cookies)->post(route('store.cart.code'), ['code' => '']);

    expect(refCookieValue($response))->toBeNull('The referral cookie must not be touched.');
    expect(StoreCart::first()->store_coupon_id)->toBeNull();

    $this->withCookies($cookies)
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.referral.code', 'KAKAMORA')
            ->where('quote.coupon_discount', 0)
        );
});

test('a referral code typed into the coupon box is redirected rather than refused', function () {
    // Two fields means one can be used by mistake. Being told a real code does not exist is the
    // worst possible answer.
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    $cookies = refGuestCart([], $package);

    $this->withCookies($cookies)
        ->post(route('store.cart.code'), ['code' => 'KAKAMORA'])
        ->assertSessionHas('toast.title', 'That is a referral code');

    expect(StoreCart::first()->store_coupon_id)->toBeNull();
    expect(StoreCart::first()->store_referral_id)->toBeNull();
});

test('an unrecognised referral code says so', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $cookies = refGuestCart([], $package);

    $this->withCookies($cookies)
        ->post(route('store.cart.referral.store'), ['code' => 'NOSUCHTHING'])
        ->assertSessionHas('toast.title', 'Invalid referral code');

    expect(StoreCart::first()->store_referral_id)->toBeNull();
});

test('the referral field is offered only where there are codes to type', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $cookies = refGuestCart([], $package);

    $this->withCookies($cookies)->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('acceptsReferralCodes', false));

    StoreReferral::factory()->create();

    $this->withCookies($cookies)->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('acceptsReferralCodes', true));

    // A disabled code is not one anybody can type, so it does not bring the field back.
    StoreReferral::query()->update(['is_enabled' => false]);

    $this->withCookies($cookies)->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('acceptsReferralCodes', false));
});

test('removing the referral leaves a coupon the buyer typed in place', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreCoupon::factory()->create(['code' => 'MINE', 'discount_value' => 1000]);
    StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    $cookies = refGuestCart(refCookie('KAKAMORA'), $package);
    $this->withCookies($cookies)->post(route('store.cart.code'), ['code' => 'MINE']);

    $response = $this->withCookies($cookies)->delete(route('store.cart.referral.delete'));

    expect(refCookieValue($response))->toBeNull();
    expect(StoreCart::first()->store_coupon_id)->not->toBeNull();

    // The browser has dropped the ref cookie. Overwritten rather than omitted, because
    // withCookies() merges into the jar the earlier calls filled — leaving it out would keep it.
    $this->withCookies([StoreReferralService::COOKIE => ''])
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.referral', null)
            ->where('quote.coupon_discount', 100)
        );
});

test('a member gets no credit for buying through their own code', function () {
    // Otherwise a code holder can stand their own discount up against their own commission and buy
    // at a permanent markdown, which is not a referral programme.
    $member = User::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->forUser($member)->create(['code' => 'SELFIE']);

    $this->actingAs($member)
        ->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->actingAs($member)
        ->withCookies(refCookie('SELFIE'))
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.referral', null));
});

test('someone else buying through that member code is credited normally', function () {
    $member = User::factory()->create();
    $buyer = User::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    StoreReferral::factory()->forUser($member)->create(['code' => 'SELFIE']);

    $this->actingAs($buyer)
        ->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->actingAs($buyer)
        ->withCookies(refCookie('SELFIE'))
        ->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.referral.code', 'SELFIE'));
});

test('an empty cart still reports a referral key', function () {
    // The cart page reads quote.referral unconditionally, so the shape cannot depend on whether a
    // cart row happens to exist.
    expect(app(StoreCartService::class)->emptyQuote())->toHaveKey('referral');
    expect(app(StoreCartService::class)->emptyQuote()['referral'])->toBeNull();
});

<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCart;
use App\Models\StoreCartItem;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Services\StoreCartService;
use App\Services\StoreCurrencyService;
use App\Services\StorePackagePresenter;
use App\Services\StoreReferralService;
use App\Services\StoreVariableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StoreCartController extends Controller
{
    public function __construct(
        private StoreCartService $carts,
        private StoreCurrencyService $currencies,
        private StoreVariableService $variables,
        private StorePackagePresenter $presenter,
        private StoreReferralService $referrals,
    ) {}

    public function show(Request $request): Response|JsonResponse
    {
        $this->authorize('browse', StorePackage::class);

        // create: false — merely looking at an empty cart should not litter a row for every
        // visitor who wanders onto the page.
        $cart = $this->carts->current($request, create: false);
        $currency = $this->currencies->resolve();

        if ($cart) {
            $this->rememberCart($cart->session_token);
        }

        $quote = $cart
            ? $this->carts->quote($cart, $request)
            : $this->carts->emptyQuote();

        if ($request->wantsJson()) {
            return response()->json(['quote' => $quote]);
        }

        return Inertia::render('Store/CartStore', [
            'quote' => $quote,
            // The highest-intent screen in the store had nothing on it but what the shopper had
            // already chosen. An empty cart gets the shelf too — it is the only thing on that page
            // worth clicking.
            'recommended' => $this->presenter->recommended(
                $currency,
                $cart ? $cart->items->pluck('store_package_id')->all() : [],
            ),
            'currency' => [
                'current' => $currency->code,
                'symbol' => $currency->symbol,
                'available' => $this->currencies->enabled()->map->only(['code', 'name', 'symbol'])->values(),
            ],
            // Whether to offer the referral field at all. A store running no creator codes should
            // not carry a permanently useless box through its highest-intent screen.
            'acceptsReferralCodes' => StoreReferral::where('is_enabled', true)->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $validated = $request->validate([
            'package_id' => 'required|integer|exists:store_packages,id',
            'quantity' => 'required|integer|min:1|max:9999',
            // A decimal amount for a pay-what-you-want package, in the currency on screen. The
            // conversion to minor units happens here, never in the browser.
            'custom_price' => 'nullable|numeric|min:0',
            // Values for the package's variables, keyed by identifier. Validated against the
            // variable definitions below — the rules the browser enforced do not count.
            'variables' => 'nullable|array',
        ]);

        $package = StorePackage::available()->with('variables')->findOrFail($validated['package_id']);

        if ($package->requires_login && ! $request->user()) {
            return redirect()->route('login')
                ->with(['toast' => ['type' => 'error', 'title' => __('Sign in required'), 'body' => __('This package can only be purchased by members.')]]);
        }

        if ($this->presenter->isOutOfStock($package)) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Out of stock'), 'body' => __('This package is no longer available.')]]);
        }

        $currency = $this->currencies->resolve();
        $customPrice = $package->is_pay_what_you_want
            ? $this->resolveCustomPrice($package, $validated['custom_price'] ?? null, $currency)
            : null;

        $variableValues = $this->variables->validate($package, $validated['variables'] ?? []);

        $cart = $this->carts->current($request);
        $this->carts->add($cart, $package, $validated['quantity'], $customPrice, $currency->code, $variableValues);
        $this->rememberCart($cart->session_token);

        // Back where they were, not off to the cart. Being ejected from the catalogue after every
        // add is what keeps a basket at one item; the toast and the navbar badge are the receipt,
        // and the storefront's own cart bar is the way on to checkout.
        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Added to cart'), 'body' => $package->name]]);
    }

    public function update(Request $request, StoreCartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $validated = $request->validate(['quantity' => 'required|integer|min:0|max:9999']);

        $this->carts->updateQuantity($cartItem, $validated['quantity']);

        return redirect()->route('store.cart.show');
    }

    public function destroy(Request $request, StoreCartItem $cartItem): RedirectResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $cartItem->delete();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Removed from cart')]]);
    }

    /**
     * Apply a coupon or gift card code.
     *
     * The box stays open once a code lands, because a basket may hold one exclusive coupon plus any
     * number of stackable ones. Each applied code carries its own way back off, so there is nothing
     * left for a shared "Clear" to be ambiguous about — which is also why referral codes keep a
     * field of their own: they are attribution rather than a discount, and a buyer may well want to
     * keep one while dropping the other.
     */
    public function applyCode(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $validated = $request->validate(['code' => 'required|string|max:64']);
        $cart = $this->carts->current($request);
        $code = trim($validated['code']);

        if ($coupon = StoreCoupon::where('code', strtoupper($code))->first()) {
            return $this->attachCoupon($cart, $coupon);
        }

        if ($giftCard = StoreGiftCard::where('code', $code)->where('is_enabled', true)->first()) {
            $cart->update(['store_gift_card_id' => $giftCard->id]);

            return redirect()->route('store.cart.show');
        }

        // Named so a buyer who puts a creator code in the wrong box is told where it goes, rather
        // than being told it does not exist.
        if ($this->referrals->findByCode($code)) {
            return redirect()->route('store.cart.show')->with(['toast' => [
                'type' => 'error',
                'title' => __('That is a referral code'),
                'body' => __('Enter it in the referral field instead.'),
            ]]);
        }

        return redirect()->route('store.cart.show')
            ->with(['toast' => ['type' => 'error', 'title' => __('Invalid code'), 'body' => __('That code was not recognised.')]]);
    }

    /**
     * Take one coupon back off the basket.
     */
    public function removeCoupon(Request $request, StoreCoupon $coupon): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        if ($cart = $this->carts->current($request, create: false)) {
            $this->carts->detachCoupon($cart, $coupon->id);
        }

        return redirect()->route('store.cart.show');
    }

    /**
     * Take the gift card back off. Its own endpoint, because it is a single slot rather than one of
     * a set, and it comes off the total in a different place.
     */
    public function removeGiftCard(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $this->carts->current($request, create: false)?->update(['store_gift_card_id' => null]);

        return redirect()->route('store.cart.show');
    }

    /**
     * Attach a coupon, and say what happened when it was not simply added.
     */
    private function attachCoupon(StoreCart $cart, StoreCoupon $coupon): RedirectResponse
    {
        // A creator's perk is not a code to be typed. It rides in with the referral, and letting a
        // buyer apply it directly would hand out the discount while the creator earned nothing —
        // which is worse now that it stacks with everything else rather than displacing it.
        if (StoreReferral::where('store_coupon_id', $coupon->id)->exists()) {
            return redirect()->route('store.cart.show')->with(['toast' => [
                'type' => 'error',
                'title' => __('That is a referral reward'),
                'body' => __('It applies on its own when you use the creator code it belongs to.'),
            ]]);
        }

        $result = $this->carts->attachCoupon($cart, $coupon);

        $toast = match ($result['status']) {
            StoreCartService::COUPON_ALREADY_APPLIED => [
                'type' => 'info',
                'title' => __('Already applied'),
                'body' => __(':code is already on your order.', ['code' => $coupon->code]),
            ],
            StoreCartService::COUPON_LIMIT_REACHED => [
                'type' => 'error',
                'title' => __('Too many codes'),
                'body' => __('You cannot apply more than :count codes to one order.', [
                    'count' => (int) config('store.cart_max_coupons', 5),
                ]),
            ],
            // Told rather than left to be noticed: a voucher disappearing from the list without
            // explanation reads as a bug.
            StoreCartService::COUPON_REPLACED => [
                'type' => 'success',
                'title' => __('Code applied'),
                'body' => __(':old was removed, because it cannot be combined with :new.', [
                    'old' => $result['replaced']->code,
                    'new' => $coupon->code,
                ]),
            ],
            default => null,
        };

        // Whether the coupon is actually valid for this basket is decided by the pricing service,
        // which reports a reason the cart page shows on that code's own chip.
        return redirect()->route('store.cart.show')->with($toast ? ['toast' => $toast] : []);
    }

    /**
     * Credit a referrer, from a code the buyer typed.
     */
    public function applyReferral(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $validated = $request->validate(['code' => 'required|string|max:64']);
        $referral = $this->referrals->findByCode($validated['code']);

        if (! $referral) {
            return redirect()->route('store.cart.show')->with(['toast' => [
                'type' => 'error',
                'title' => __('Invalid referral code'),
                'body' => __('That referral code was not recognised.'),
            ]]);
        }

        // Written to the cart rather than the cookie: a code the buyer chose deliberately should
        // outlive the attribution window of whatever link they happened to arrive through.
        $this->carts->current($request)->update(['store_referral_id' => $referral->id]);

        return redirect()->route('store.cart.show');
    }

    /**
     * Stop crediting the referrer, leaving any coupon or gift card alone.
     *
     * Forgets the cookie too, not just the cart row — otherwise a buyer who removed a referrer they
     * arrived through would find them credited again on the next page load.
     */
    public function clearReferral(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $this->carts->current($request)->update(['store_referral_id' => null]);
        $this->referrals->forget($request);

        return redirect()->route('store.cart.show');
    }

    /**
     * Turn the buyer's typed amount into minor units of the currency they are shopping in.
     *
     * The configured price is the floor, and being told so beats being silently charged more than
     * you typed.
     *
     * @throws ValidationException
     */
    private function resolveCustomPrice(StorePackage $package, int|float|string|null $amount, StoreCurrency $currency): int
    {
        $minimum = $this->currencies->priceForPackage($package, $currency);

        if ($amount === null || $amount === '') {
            return $minimum;
        }

        $minor = $this->currencies->toMinor($amount, $currency);

        if ($minor < $minimum) {
            throw ValidationException::withMessages([
                'custom_price' => __('Please enter at least :amount.', [
                    'amount' => $this->currencies->format($minimum, $currency),
                ]),
            ]);
        }

        if ($package->pay_what_you_want_max) {
            $cap = max($minimum, $this->currencies->fromBase((int) $package->pay_what_you_want_max, $currency));

            if ($minor > $cap) {
                throw ValidationException::withMessages([
                    'custom_price' => __('Please enter no more than :amount.', [
                        'amount' => $this->currencies->format($cap, $currency),
                    ]),
                ]);
            }
        }

        return $minor;
    }

    /**
     * A cart item is only addressable by whoever owns its cart. Guest carts are keyed on an
     * opaque cookie token, so knowing an item id is not enough.
     */
    private function authorizeCartItem(Request $request, StoreCartItem $item): void
    {
        $this->authorize('browse', StorePackage::class);

        $cart = $this->carts->current($request, create: false);

        abort_unless($cart && $item->store_cart_id === $cart->id, 403);
    }

    /**
     * Queue the guest cart cookie.
     *
     * Queued rather than attached to the response: Inertia\Response has no withCookie(), and
     * queueing works uniformly for both Inertia renders and redirects.
     */
    private function rememberCart(?string $token): void
    {
        if (! $token) {
            return;
        }

        Cookie::queue(
            StoreCartService::COOKIE,
            $token,
            60 * 24 * (int) config('store.cart_ttl_days', 30),
        );
    }
}

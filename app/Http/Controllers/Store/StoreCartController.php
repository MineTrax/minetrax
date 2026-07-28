<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCartItem;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Services\StoreCartService;
use App\Services\StoreCurrencyService;
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
    ) {}

    public function show(Request $request): Response
    {
        $this->authorize('browse', StorePackage::class);

        // create: false — merely looking at an empty cart should not litter a row for every
        // visitor who wanders onto the page.
        $cart = $this->carts->current($request, create: false);
        $currency = $this->currencies->resolve();

        if ($cart) {
            $this->rememberCart($cart->session_token);
        }

        return Inertia::render('Store/CartStore', [
            'quote' => $cart
                ? $this->carts->quote($cart, $request)
                : $this->carts->emptyQuote(),
            'currency' => [
                'current' => $currency->code,
                'symbol' => $currency->symbol,
                'available' => $this->currencies->enabled()->map->only(['code', 'name', 'symbol'])->values(),
            ],
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
        ]);

        $package = StorePackage::available()->findOrFail($validated['package_id']);

        if ($package->requires_login && ! $request->user()) {
            return redirect()->route('login')
                ->with(['toast' => ['type' => 'error', 'title' => __('Sign in required'), 'body' => __('This package can only be purchased by members.')]]);
        }

        if ($this->isOutOfStock($package)) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Out of stock'), 'body' => __('This package is no longer available.')]]);
        }

        $currency = $this->currencies->resolve();
        $customPrice = $package->is_pay_what_you_want
            ? $this->resolveCustomPrice($package, $validated['custom_price'] ?? null, $currency)
            : null;

        $cart = $this->carts->current($request);
        $this->carts->add($cart, $package, $validated['quantity'], $customPrice, $currency->code);
        $this->rememberCart($cart->session_token);

        return redirect()->route('store.cart.show')
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

        return redirect()->route('store.cart.show')
            ->with(['toast' => ['type' => 'success', 'title' => __('Removed from cart')]]);
    }

    /**
     * Apply or clear a coupon / gift card code.
     */
    public function applyCode(Request $request): RedirectResponse
    {
        $this->authorize('browse', StorePackage::class);

        $validated = $request->validate(['code' => 'nullable|string|max:64']);
        $cart = $this->carts->current($request);
        $code = trim((string) ($validated['code'] ?? ''));

        if ($code === '') {
            $cart->update(['store_coupon_id' => null, 'store_gift_card_id' => null]);

            return redirect()->route('store.cart.show');
        }

        if ($coupon = StoreCoupon::where('code', strtoupper($code))->first()) {
            $cart->update(['store_coupon_id' => $coupon->id]);

            // Whether the coupon is actually valid for this basket is decided by the pricing
            // service, which reports a reason the cart page can show.
            return redirect()->route('store.cart.show');
        }

        if ($giftCard = StoreGiftCard::where('code', $code)->where('is_enabled', true)->first()) {
            $cart->update(['store_gift_card_id' => $giftCard->id]);

            return redirect()->route('store.cart.show');
        }

        return redirect()->route('store.cart.show')
            ->with(['toast' => ['type' => 'error', 'title' => __('Invalid code'), 'body' => __('That code was not recognised.')]]);
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
     * Mirrors StoreController: only a lifetime global limit reads as out of stock.
     */
    private function isOutOfStock(StorePackage $package): bool
    {
        return $package->global_purchase_limit !== null
            && $package->global_purchase_limit_period_days === null
            && $package->sold_count >= $package->global_purchase_limit;
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

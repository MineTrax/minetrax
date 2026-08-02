<?php

namespace App\Services;

use App\Models\StoreCart;
use App\Models\StoreCartItem;
use App\Models\StoreCoupon;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreCartService
{
    public const COOKIE = 'store_cart_token';

    public const COUPON_ATTACHED = 'attached';

    public const COUPON_ALREADY_APPLIED = 'already_applied';

    public const COUPON_REPLACED = 'replaced';

    public const COUPON_LIMIT_REACHED = 'limit_reached';

    public function __construct(
        private StorePricingService $pricing,
        private StoreVariableService $variables,
    ) {}

    /**
     * The current visitor's cart, creating one on demand.
     *
     * A logged-in user has exactly one cart keyed by user_id; a guest's is keyed by an opaque
     * cookie token. Both columns are unique, so a visitor can never end up with two carts.
     */
    public function current(Request $request, bool $create = true): ?StoreCart
    {
        if ($user = $request->user()) {
            return $create
                ? StoreCart::firstOrCreate(['user_id' => $user->id])
                : StoreCart::where('user_id', $user->id)->first();
        }

        $token = $request->cookie(self::COOKIE);

        if ($token) {
            $cart = StoreCart::where('session_token', $token)->first();
            if ($cart) {
                return $cart;
            }
        }

        if (! $create) {
            return null;
        }

        return StoreCart::create(['session_token' => $token ?: Str::random(64)]);
    }

    /**
     * Add a line, bumping the quantity when the package is already in the cart.
     *
     * $customPrice is the buyer's chosen amount for a pay-what-you-want package, in the minor
     * units of $customPriceCurrency. Re-adding such a package replaces the amount rather than
     * summing it, because two different chosen amounts have no meaningful sum. The same goes for
     * variable values: one configuration per package per cart.
     *
     * @param  array<string, mixed>|null  $variableValues
     */
    public function add(
        StoreCart $cart,
        StorePackage $package,
        int $quantity,
        ?int $customPrice = null,
        ?string $customPriceCurrency = null,
        ?array $variableValues = null,
    ): StoreCartItem {
        $item = $cart->items()
            ->where('store_package_id', $package->id)
            ->first();

        $quantity = $this->clampQuantity($package, ($item->quantity ?? 0) + $quantity);

        $attributes = [
            'quantity' => $quantity,
            'variable_values' => $variableValues,
        ];

        if ($package->is_pay_what_you_want) {
            $attributes['custom_price'] = $customPrice;
            $attributes['custom_price_currency'] = $customPrice === null ? null : strtoupper((string) $customPriceCurrency);
        }

        if ($item) {
            $item->update($attributes);

            return $item;
        }

        if ($cart->items()->count() >= config('store.cart_max_items', 20)) {
            abort(422, __('Your cart is full.'));
        }

        return $cart->items()->create($attributes + ['store_package_id' => $package->id]);
    }

    public function updateQuantity(StoreCartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $this->clampQuantity($item->package, $quantity)]);
    }

    /**
     * Merge a guest cart into the user's own cart at login.
     *
     * Called from the Login listener. Quantities are summed and re-clamped rather than replaced,
     * so a buyer who filled a cart before signing in does not lose it.
     */
    public function mergeGuestCartInto(User $user, ?string $guestToken): void
    {
        if (! $guestToken) {
            return;
        }

        $guestCart = StoreCart::with('items')->where('session_token', $guestToken)->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            $guestCart?->delete();

            return;
        }

        DB::transaction(function () use ($user, $guestCart) {
            $userCart = StoreCart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()
                    ->where('store_package_id', $guestItem->store_package_id)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => $this->clampQuantity($existing->package, $existing->quantity + $guestItem->quantity),
                        // The guest's chosen pay-what-you-want amount wins: it is the more recent
                        // decision, and the two amounts cannot be summed sensibly.
                        'custom_price' => $guestItem->custom_price ?? $existing->custom_price,
                        'custom_price_currency' => $guestItem->custom_price_currency ?? $existing->custom_price_currency,
                        'variable_values' => $guestItem->variable_values ?? $existing->variable_values,
                    ]);

                    continue;
                }

                $userCart->items()->create($guestItem->only([
                    'store_package_id', 'quantity', 'custom_price', 'custom_price_currency', 'variable_values',
                ]));
            }

            $guestCart->delete();
        });
    }

    /**
     * A priced snapshot of the cart for display. Always computed live — cart rows deliberately
     * store no prices.
     *
     * @return array<string, mixed>
     */
    public function quote(StoreCart $cart, ?Request $request = null): array
    {
        $cart->loadMissing(['coupons', 'items.package.prices', 'items.package.variables', 'items.package.category']);

        $lines = $cart->items
            ->filter(fn (StoreCartItem $item) => $item->package && $item->package->is_available)
            ->map(fn (StoreCartItem $item) => [
                'cart_item_id' => $item->id,
                'package' => $item->package,
                'quantity' => $item->quantity,
                'custom_price' => $item->custom_price,
                'custom_price_currency' => $item->custom_price_currency,
                'variable_values' => $item->variable_values,
            ])
            ->values();

        // Who is being credited, and what they hand the buyer for it. Resolved here rather than in
        // the pricing service, which does money and must not learn about cookies.
        $referral = $request
            ? app(StoreReferralService::class)->resolveFor($request, $cart)['referral']
            : null;

        $quote = $this->pricing->quote(
            $lines->all(),
            null,
            $this->couponsFor($cart, $referral),
            $cart->giftCard,
            $request?->user(),
            $this->indicativePlayerUuid($request),
            // Indicative only, from the visitor's IP. Checkout re-quotes against the country
            // recorded on the order, and that figure is the authoritative one.
            $request ? app(GeolocationService::class)->getCountryIdFromIP($request->ip()) : null,
        );

        // Presentational, beside the applied coupons. The cart shows who a purchase would
        // support, and offers a way out of it — a referral picked up from a link is invisible
        // otherwise.
        $quote['referral'] = $referral ? [
            'code' => $referral->code,
            'referrer_name' => $referral->referrer_name,
            // Which of the coupons in the quote came from the referral rather than from the buyer.
            // That chip carries no × of its own: the perk belongs to the code, and giving up the
            // code is what gives up the perk.
            'coupon_id' => $referral->store_coupon_id,
        ] : null;

        // Re-attach the cart item id so the UI can address each row.
        foreach ($quote['items'] as $index => $item) {
            $package = $lines[$index]['package'] ?? null;

            $quote['items'][$index]['cart_item_id'] = $lines[$index]['cart_item_id'] ?? null;
            $quote['items'][$index]['photo_url'] = $package->photo_url ?? null;
            $quote['items'][$index]['slug'] = $package->slug ?? null;
            // The cart's stepper used to floor at 1 and have no ceiling, so a package sold in
            // fives could be edited down to one and a capped one bought past its cap — both only
            // to be clamped, unexplained, by updateQuantity().
            $quote['items'][$index]['min_quantity'] = $package?->min_quantity ?? 1;
            $quote['items'][$index]['max_quantity'] = $package?->max_quantity;
            // Named rather than keyed by identifier: this is for a buyer to read.
            $quote['items'][$index]['variables'] = $package
                ? $this->variables->snapshotFor($package, $lines[$index]['variable_values'] ?? null)
                : null;
        }

        return $quote;
    }

    /**
     * Attach a coupon to the cart, enforcing the one-exclusive rule.
     *
     * A stackable coupon joins whatever is already there. An exclusive one displaces the exclusive
     * coupon already attached, if any — a plain voucher has always behaved that way, and the caller
     * is handed what it replaced so the buyer is told rather than watching a code silently vanish.
     *
     * @return array{status: string, replaced: StoreCoupon|null}
     */
    public function attachCoupon(StoreCart $cart, StoreCoupon $coupon): array
    {
        $attached = $cart->coupons()->get();

        if ($attached->contains('id', $coupon->id)) {
            return ['status' => self::COUPON_ALREADY_APPLIED, 'replaced' => null];
        }

        $replaced = $coupon->isStackable()
            ? null
            : $attached->first(fn (StoreCoupon $existing) => ! $existing->isStackable());

        if ($replaced) {
            $cart->coupons()->detach($replaced->id);
        }

        // A swap is never blocked by the cap: it leaves the count where it was. The cap counts what
        // the buyer attached, so a referral's perk — which rides along without being attached —
        // cannot use up their budget.
        if (! $replaced && $attached->count() >= (int) config('store.cart_max_coupons', 5)) {
            return ['status' => self::COUPON_LIMIT_REACHED, 'replaced' => null];
        }

        $cart->coupons()->attach($coupon->id);
        // Attaching through a pivot leaves the cart's own timestamp alone, and `updated_at` is what
        // the pruner reads: without this, a buyer who spent a while hunting for a code could have
        // the basket swept out from under them.
        $cart->touch();
        $cart->load('coupons');

        return [
            'status' => $replaced ? self::COUPON_REPLACED : self::COUPON_ATTACHED,
            'replaced' => $replaced,
        ];
    }

    public function detachCoupon(StoreCart $cart, int $couponId): void
    {
        $cart->coupons()->detach($couponId);
        $cart->touch();
        $cart->load('coupons');
    }

    /**
     * Every coupon that should price this basket.
     *
     * The buyer's own, plus the perk the referral hands out. The perk rides on top rather than
     * competing for a slot — that is what makes it a stackable coupon — and is appended here rather
     * than attached to the cart, because it is not the buyer's to keep: dropping the referral drops
     * the perk with it.
     *
     * @return Collection<int, StoreCoupon>
     */
    public function couponsFor(StoreCart $cart, ?StoreReferral $referral): Collection
    {
        $coupons = $cart->coupons;

        if ($perk = $referral?->coupon) {
            $coupons = $coupons->merge([$perk]);
        }

        return $coupons->unique('id')->values();
    }

    /**
     * A zero-value quote, for a visitor who has no cart row at all.
     *
     * @return array<string, mixed>
     */
    public function emptyQuote(): array
    {
        // The referral key is present but null, so the cart page reads the same shape whether or
        // not a cart row exists.
        return $this->pricing->quote([]) + ['referral' => null];
    }

    public function itemCount(?StoreCart $cart): int
    {
        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    /**
     * Who the cart is probably for, so an upgrade credit can be shown before checkout.
     *
     * The delivery player is not decided until checkout, where the credit is computed again against
     * whoever is actually named — that figure is the authoritative one. This only fills the gap for
     * the common case of a signed-in buyer with a single linked player; with none, or several, the
     * cart shows the undiscounted price rather than guessing which player to credit.
     */
    private function indicativePlayerUuid(?Request $request): ?string
    {
        $players = $request?->user()?->players;

        return $players?->count() === 1 ? $players->first()->uuid : null;
    }

    private function clampQuantity(StorePackage $package, int $quantity): int
    {
        // One line, one chosen amount: a quantity would make "pay what you want" ambiguous.
        if ($package->is_pay_what_you_want) {
            return 1;
        }

        $quantity = max($package->min_quantity ?? 1, $quantity);

        if ($package->max_quantity) {
            $quantity = min($package->max_quantity, $quantity);
        }

        return $quantity;
    }
}

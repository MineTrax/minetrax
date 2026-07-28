<?php

namespace App\Services;

use App\Models\StoreCart;
use App\Models\StoreCartItem;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreCartService
{
    public const COOKIE = 'store_cart_token';

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
        $cart->loadMissing(['items.package.prices', 'items.package.variables']);

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

        $quote = $this->pricing->quote(
            $lines->all(),
            null,
            $cart->coupon,
            $cart->giftCard,
            $request?->user(),
        );

        // Re-attach the cart item id so the UI can address each row.
        foreach ($quote['items'] as $index => $item) {
            $package = $lines[$index]['package'] ?? null;

            $quote['items'][$index]['cart_item_id'] = $lines[$index]['cart_item_id'] ?? null;
            $quote['items'][$index]['photo_url'] = $package->photo_url ?? null;
            $quote['items'][$index]['slug'] = $package->slug ?? null;
            // Named rather than keyed by identifier: this is for a buyer to read.
            $quote['items'][$index]['variables'] = $package
                ? $this->variables->snapshotFor($package, $lines[$index]['variable_values'] ?? null)
                : null;
        }

        return $quote;
    }

    /**
     * A zero-value quote, for a visitor who has no cart row at all.
     *
     * @return array<string, mixed>
     */
    public function emptyQuote(): array
    {
        return $this->pricing->quote([]);
    }

    public function itemCount(?StoreCart $cart): int
    {
        return $cart ? (int) $cart->items()->sum('quantity') : 0;
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

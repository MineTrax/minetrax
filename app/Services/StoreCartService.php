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

    public function __construct(private StorePricingService $pricing) {}

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
     */
    public function add(StoreCart $cart, StorePackage $package, int $quantity): StoreCartItem
    {
        $item = $cart->items()
            ->where('store_package_id', $package->id)
            ->first();

        $quantity = $this->clampQuantity($package, ($item->quantity ?? 0) + $quantity);

        if ($item) {
            $item->update(['quantity' => $quantity]);

            return $item;
        }

        if ($cart->items()->count() >= config('store.cart_max_items', 20)) {
            abort(422, __('Your cart is full.'));
        }

        return $cart->items()->create([
            'store_package_id' => $package->id,
            'quantity' => $quantity,
        ]);
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
                    ]);

                    continue;
                }

                $userCart->items()->create($guestItem->only(['store_package_id', 'quantity']));
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
        $cart->loadMissing('items.package.prices');

        $lines = $cart->items
            ->filter(fn (StoreCartItem $item) => $item->package && $item->package->is_enabled)
            ->map(fn (StoreCartItem $item) => [
                'cart_item_id' => $item->id,
                'package' => $item->package,
                'quantity' => $item->quantity,
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
            $quote['items'][$index]['cart_item_id'] = $lines[$index]['cart_item_id'] ?? null;
            $quote['items'][$index]['photo_url'] = $lines[$index]['package']->photo_url ?? null;
            $quote['items'][$index]['slug'] = $lines[$index]['package']->slug ?? null;
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
        $quantity = max($package->min_quantity ?? 1, $quantity);

        if ($package->max_quantity) {
            $quantity = min($package->max_quantity, $quantity);
        }

        return $quantity;
    }
}

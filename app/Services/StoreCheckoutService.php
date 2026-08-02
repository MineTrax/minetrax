<?php

namespace App\Services;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePaymentStatus;
use App\Models\StoreCart;
use App\Models\StoreCoupon;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use App\Models\StorePackageGrant;
use App\Models\StoreReferral;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Turns a cart into a PENDING order.
 *
 * The basket is revalidated and re-quoted inside the transaction, so the totals written to the
 * order are computed from live database state at the moment of purchase — never from anything the
 * client sent, and never from a quote that was rendered minutes ago.
 */
class StoreCheckoutService
{
    public function __construct(
        private StorePricingService $pricing,
        private StoreCurrencyService $currencies,
        private StoreCartService $carts,
        private StoreBanService $bans,
        private StoreVariableService $variables,
        private StoreSettings $settings,
    ) {}

    /**
     * @param  array{player_username: string, email?: string|null, gateway: string, ip?: string|null, user_agent?: string|null, country_id?: int|null, referral?: StoreReferral|null, referral_source?: string|null}  $input
     *
     * @throws ValidationException
     */
    public function placeOrder(StoreCart $cart, array $input, ?User $user, array $resolvedPlayer): StoreOrder
    {
        $cart->loadMissing([
            'items.package.prices',
            'items.package.requiredPackages',
            'items.package.variables',
            'items.package.category',
        ]);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => __('Your cart is empty.')]);
        }

        if ($ban = $this->bans->match($user, $resolvedPlayer['uuid'], $input['ip'] ?? null, $input['email'] ?? null)) {
            throw ValidationException::withMessages([
                'cart' => $ban->reason
                    ? __('You cannot place an order: :reason', ['reason' => $ban->reason])
                    : __('You cannot place an order at this time.'),
            ]);
        }

        return DB::transaction(function () use ($cart, $input, $user, $resolvedPlayer) {
            $currency = $this->currencies->resolve();

            $lines = [];
            foreach ($cart->items as $item) {
                $package = $item->package;

                if (! $package || ! $package->is_available) {
                    throw ValidationException::withMessages([
                        'cart' => __('":name" is no longer available.', ['name' => $item->package->name ?? __('An item')]),
                    ]);
                }

                $this->assertPurchasable($package, $item->quantity, $resolvedPlayer['uuid']);

                $lines[] = [
                    'package' => $package,
                    'quantity' => $item->quantity,
                    'custom_price' => $item->custom_price,
                    'custom_price_currency' => $item->custom_price_currency,
                    // Revalidated here, not trusted from the cart row: a variable may have been
                    // added, made required or had its choices changed since the item was carted.
                    'variable_values' => $this->variables->validate($package, $item->variable_values ?? []),
                ];
            }

            $this->assertRequirementsMet($lines, $resolvedPlayer['uuid']);
            $this->assertDeliveryTargetAllowed($lines, $user, $resolvedPlayer['uuid']);

            // Re-quoted here, inside the transaction, from live prices — and against the player who
            // was actually named, which is what makes the upgrade credit authoritative rather than
            // the indicative figure the cart page showed.
            // Resolved by the controller, which is the only place the cookie is in scope. Null when
            // nobody is being credited, or when the buyer holds the code themselves.
            $referral = $input['referral'] ?? null;

            $quote = $this->pricing->quote(
                $lines,
                $currency,
                // Same precedence the cart showed: a coupon the buyer typed beats the one a
                // referral hands out. Re-derived here rather than passed in, so the price charged
                // cannot disagree with the price quoted.
                $cart->coupon ?? $referral?->coupon,
                $cart->giftCard,
                $user,
                $resolvedPlayer['uuid'],
                $input['country_id'] ?? null,
            );

            if ($quote['coupon_error']) {
                throw ValidationException::withMessages(['code' => $quote['coupon_error']]);
            }

            $order = StoreOrder::create([
                'user_id' => $user?->id,
                'email' => $input['email'] ?? $user?->email,
                'player_id' => $resolvedPlayer['player']?->id,
                'player_uuid' => $resolvedPlayer['uuid'],
                'player_username' => $resolvedPlayer['username'],
                'currency' => $quote['currency'],
                'base_currency' => $quote['base_currency'],
                'exchange_rate' => $quote['exchange_rate'],
                'subtotal' => $quote['subtotal'],
                'sale_discount' => $quote['sale_discount'],
                'coupon_discount' => $quote['coupon_discount'],
                'tax_amount' => $quote['tax_amount'],
                // Snapshotted with the order: the rule may be edited, disabled or deleted, and a
                // receipt has to keep saying what was actually charged.
                'tax_name' => $quote['tax_name'],
                'tax_rate_bp' => $quote['tax_rate_bp'],
                'tax_is_inclusive' => $quote['tax_is_inclusive'],
                'total' => $quote['total'],
                'gift_card_amount' => $quote['gift_card_amount'],
                'amount_due' => $quote['amount_due'],
                'base_total' => $quote['base_total'],
                'store_coupon_id' => $quote['coupon_discount'] > 0 ? ($cart->store_coupon_id ?? $referral?->store_coupon_id) : null,
                'coupon_code' => $quote['coupon_code'],
                'store_gift_card_id' => $quote['gift_card_amount'] > 0 ? $cart->store_gift_card_id : null,
                // Attributed now, while the cookie is still in scope. What it *earns* is worked out
                // at payment, because nothing is owed on an order nobody paid for.
                //
                // share_bp is snapshotted beside the id for the same reason coupon_code is:
                // changing a referral's cut later must not re-price what it already earned.
                'store_referral_id' => $referral?->id,
                'referral_code' => $referral?->code,
                'referral_share_bp' => $referral?->share_bp,
                'referral_source' => $referral ? ($input['referral_source'] ?? null) : null,
                'status' => StoreOrderStatus::PENDING,
                'delivery_status' => StoreDeliveryStatus::PENDING,
                'gateway' => $input['gateway'],
                'ip_address' => $input['ip'] ?? null,
                'user_agent' => $input['user_agent'] ?? null,
                'country_id' => $input['country_id'] ?? null,
                // Empty unless the store collects one. Spread rather than listed field by field so
                // the caller decides what an address is, and adding a line never means touching
                // both ends.
                ...($input['billing'] ?? []),
            ]);

            foreach ($quote['items'] as $index => $item) {
                $package = $lines[$index]['package'];

                $order->items()->create([
                    'store_package_id' => $package->id,
                    'package_name' => $item['package_name'],
                    'quantity' => $item['quantity'],
                    'unit_price_original' => $item['unit_price_original'],
                    'unit_price' => $item['unit_price'],
                    'upgrade_credit' => $item['upgrade_credit'],
                    'total' => $item['total'],
                    // The id as well as the name: the sale's commands resolve against this at
                    // delivery, and again on a refund long after the sale has ended.
                    'store_sale_id' => $item['sale_id'],
                    'sale_name' => $item['sale_name'],
                    'expiry_duration_days' => $package->expiry_duration_days,
                    // Snapshotted with names, so the order still reads correctly after a variable
                    // is renamed or deleted.
                    'variable_values' => $this->variables->snapshotFor($package, $lines[$index]['variable_values']),
                ]);
            }

            // Reserved now, not at payment: two buyers racing for the last use of a
            // limited coupon must not both get it.
            $this->reserveCoupon($order);

            $payment = $order->payments()->create([
                'gateway' => $input['gateway'],
                'status' => StorePaymentStatus::PENDING,
                'amount' => $order->amount_due,
                'currency' => $order->currency,
            ]);

            $cart->items()->delete();
            $cart->update(['store_coupon_id' => null, 'store_gift_card_id' => null]);

            $order->setRelation('pendingPayment', $payment);

            return $order;
        });
    }

    /**
     * The two purchase limits: how much this one player may buy, and how much everyone may buy in
     * total. Each is a quantity cap over a rolling window, and a null window never resets — which
     * is how a fixed stock is expressed.
     *
     * Counted from paid-state orders only, so an abandoned order neither holds stock nor burns a
     * buyer's allowance, and a cancellation restocks automatically.
     *
     * @throws ValidationException
     */
    private function assertPurchasable(StorePackage $package, int $quantity, string $playerUuid): void
    {
        if ($package->global_purchase_limit !== null) {
            $sold = $this->soldQuantity($package, $package->global_purchase_limit_period_days);

            if ($sold + $quantity > $package->global_purchase_limit) {
                throw ValidationException::withMessages([
                    'cart' => $package->global_purchase_limit_period_days
                        ? __('":name" has sold out for now. Please try again later.', ['name' => $package->name])
                        : __('":name" is out of stock.', ['name' => $package->name]),
                ]);
            }
        }

        if ($package->player_purchase_limit !== null) {
            $sold = $this->soldQuantity($package, $package->player_purchase_limit_period_days, $playerUuid);

            if ($sold + $quantity > $package->player_purchase_limit) {
                throw ValidationException::withMessages([
                    'cart' => __('You have reached the purchase limit for ":name".', ['name' => $package->name]),
                ]);
            }
        }
    }

    /**
     * Quantity of a package sold in paid-state orders, optionally within a rolling window and
     * optionally for one player.
     *
     * Quantity rather than order count: a limit of one has to stop a single order for five.
     */
    private function soldQuantity(StorePackage $package, ?int $periodDays, ?string $playerUuid = null): int
    {
        return (int) StoreOrderItem::query()
            ->where('store_package_id', $package->id)
            ->whereHas('order', function ($query) use ($periodDays, $playerUuid) {
                $query->whereIn('status', $this->paidStatuses());

                if ($playerUuid) {
                    $query->where('player_uuid', $playerUuid);
                }

                if ($periodDays) {
                    $query->where('created_at', '>=', now()->subDays($periodDays));
                }
            })
            ->sum('quantity');
    }

    /**
     * Packages that gate other packages.
     *
     * A requirement is satisfied by an active grant for this player, or by the required package
     * being in the same basket — buying a rank and its prerequisite together should work, and both
     * are delivered by the same fulfilment job.
     *
     * @param  array<int, array{package: StorePackage, quantity: int}>  $lines
     *
     * @throws ValidationException
     */
    private function assertRequirementsMet(array $lines, string $playerUuid): void
    {
        $basketPackageIds = array_map(fn (array $line) => $line['package']->id, $lines);

        foreach ($lines as $line) {
            /** @var StorePackage $package */
            $package = $line['package'];
            $required = $package->requiredPackages;

            if ($required->isEmpty()) {
                continue;
            }

            $ownedIds = StorePackageGrant::query()
                ->where('player_uuid', $playerUuid)
                ->where('status', StorePackageGrantStatus::ACTIVE)
                ->whereIn('store_package_id', $required->modelKeys())
                ->pluck('store_package_id')
                ->all();

            $satisfied = $required->filter(
                fn (StorePackage $requirement) => in_array($requirement->id, $ownedIds, true)
                    || in_array($requirement->id, $basketPackageIds, true)
            );

            $isMet = $package->required_packages_mode === StorePackageRequirementMode::ANY
                ? $satisfied->isNotEmpty()
                : $satisfied->count() === $required->count();

            if ($isMet) {
                continue;
            }

            $missing = $required->reject(fn (StorePackage $requirement) => $satisfied->contains($requirement));

            throw ValidationException::withMessages([
                'cart' => $package->required_packages_mode === StorePackageRequirementMode::ANY
                    ? __('":name" requires one of: :packages.', [
                        'name' => $package->name,
                        'packages' => $required->pluck('name')->join(', ', ' or '),
                    ])
                    : __('":name" requires you to own :packages first.', [
                        'name' => $package->name,
                        'packages' => $missing->pluck('name')->join(', ', ' and '),
                    ]),
            ]);
        }
    }

    /**
     * The gifting rule.
     *
     * Every checkout names the player to deliver to, so buying for someone else is inherent. What
     * is_giftable controls is whether that is allowed: a buyer with linked players may only send a
     * non-giftable package to one of their own. A guest, and a member who has linked no player at
     * all, has no identity to compare against — there is nothing to enforce against them here.
     *
     * @param  array<int, array{package: StorePackage, quantity: int}>  $lines
     *
     * @throws ValidationException
     */
    private function assertDeliveryTargetAllowed(array $lines, ?User $user, string $playerUuid): void
    {
        if (! $user) {
            return;
        }

        $ownUuids = $user->players->pluck('uuid');

        if ($ownUuids->isEmpty() || $ownUuids->contains($playerUuid)) {
            return;
        }

        foreach ($lines as $line) {
            /** @var StorePackage $package */
            $package = $line['package'];

            if (! $package->is_giftable) {
                throw ValidationException::withMessages([
                    'player_username' => __('":name" cannot be sent to another player.', ['name' => $package->name]),
                ]);
            }
        }
    }

    private function reserveCoupon(StoreOrder $order): void
    {
        if (! $order->store_coupon_id) {
            return;
        }

        /** @var StoreCoupon|null $coupon */
        $coupon = StoreCoupon::lockForUpdate()->find($order->store_coupon_id);

        if (! $coupon) {
            return;
        }

        if ($coupon->max_uses_total !== null && $coupon->used_count >= $coupon->max_uses_total) {
            throw ValidationException::withMessages(['code' => __('This code has been fully redeemed.')]);
        }

        $coupon->increment('used_count');
    }

    /**
     * @return array<int, string>
     */
    private function paidStatuses(): array
    {
        return collect(StoreOrderStatus::cases())
            ->filter(fn (StoreOrderStatus $status) => $status->isPaidState())
            ->map(fn (StoreOrderStatus $status) => $status->value)
            ->all();
    }
}

<?php

namespace App\Services;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\StoreCart;
use App\Models\StoreCoupon;
use App\Models\StoreOrder;
use App\Models\StorePackage;
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
        private StoreSettings $settings,
    ) {}

    /**
     * @param  array{player_username: string, email?: string|null, gateway: string, ip?: string|null, user_agent?: string|null, country_id?: int|null}  $input
     *
     * @throws ValidationException
     */
    public function placeOrder(StoreCart $cart, array $input, ?User $user, array $resolvedPlayer): StoreOrder
    {
        $cart->loadMissing('items.package.prices');

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

                if (! $package || ! $package->is_enabled) {
                    throw ValidationException::withMessages([
                        'cart' => __('":name" is no longer available.', ['name' => $item->package->name ?? __('An item')]),
                    ]);
                }

                $this->assertPurchasable($package, $item->quantity, $resolvedPlayer['uuid']);

                $lines[] = [
                    'package' => $package,
                    'quantity' => $item->quantity,
                ];
            }

            // Re-quoted here, inside the transaction, from live prices.
            $quote = $this->pricing->quote($lines, $currency, $cart->coupon, $cart->giftCard, $user);

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
                'total' => $quote['total'],
                'gift_card_amount' => $quote['gift_card_amount'],
                'amount_due' => $quote['amount_due'],
                'base_total' => $quote['base_total'],
                'store_coupon_id' => $quote['coupon_discount'] > 0 ? $cart->store_coupon_id : null,
                'coupon_code' => $quote['coupon_code'],
                'store_gift_card_id' => $quote['gift_card_amount'] > 0 ? $cart->store_gift_card_id : null,
                'status' => StoreOrderStatus::PENDING,
                'delivery_status' => StoreDeliveryStatus::PENDING,
                'gateway' => $input['gateway'],
                'ip_address' => $input['ip'] ?? null,
                'user_agent' => $input['user_agent'] ?? null,
                'country_id' => $input['country_id'] ?? null,
            ]);

            foreach ($quote['items'] as $index => $item) {
                $package = $lines[$index]['package'];

                $order->items()->create([
                    'store_package_id' => $package->id,
                    'package_name' => $item['package_name'],
                    'quantity' => $item['quantity'],
                    'unit_price_original' => $item['unit_price_original'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'sale_name' => $item['sale_name'],
                    'expiry_duration_days' => $package->expiry_duration_days,
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
     * Stock and per-player purchase limits. Counted from paid-state orders only, so an abandoned
     * order neither holds stock nor burns a buyer's allowance.
     *
     * @throws ValidationException
     */
    private function assertPurchasable(StorePackage $package, int $quantity, string $playerUuid): void
    {
        if ($package->stock_limit !== null && ($package->sold_count + $quantity) > $package->stock_limit) {
            throw ValidationException::withMessages([
                'cart' => __('":name" is out of stock.', ['name' => $package->name]),
            ]);
        }

        if ($package->player_purchase_limit === null) {
            return;
        }

        $query = StoreOrder::query()
            ->where('player_uuid', $playerUuid)
            ->whereIn('status', $this->paidStatuses())
            ->whereHas('items', fn ($q) => $q->where('store_package_id', $package->id));

        if ($package->purchase_limit_period_days) {
            $query->where('created_at', '>=', now()->subDays($package->purchase_limit_period_days));
        }

        if ($query->count() >= $package->player_purchase_limit) {
            throw ValidationException::withMessages([
                'cart' => __('You have reached the purchase limit for ":name".', ['name' => $package->name]),
            ]);
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

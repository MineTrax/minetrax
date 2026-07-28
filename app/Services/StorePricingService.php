<?php

namespace App\Services;

use App\Enums\StoreDiscountType;
use App\Enums\StoreTaxMode;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Support\Collection;

/**
 * The only place a basket total is computed.
 *
 * The client sends ids, quantities and codes — never amounts. Every price is re-read from the
 * database here, which is what makes a tampered cart harmless: the worst a caller can do is ask
 * for a different package, not a different price.
 *
 * All arithmetic is integer minor units; percentages are basis points (2000 = 20%).
 */
class StorePricingService
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StoreSettings $settings,
    ) {}

    /**
     * Price a basket.
     *
     * @param  array<int, array{package: StorePackage, quantity: int, custom_price?: int|null, custom_price_currency?: string|null}>  $lines
     * @return array<string, mixed>
     */
    public function quote(
        array $lines,
        ?StoreCurrency $currency = null,
        ?StoreCoupon $coupon = null,
        ?StoreGiftCard $giftCard = null,
        ?User $user = null,
    ): array {
        $currency = $currency ?? $this->currencies->resolve();
        $sales = $this->activeSales();

        $items = [];
        $subtotal = 0;
        $saleDiscount = 0;

        foreach ($lines as $line) {
            $item = $this->priceLine($line, $currency, $sales);
            $items[] = $item;
            $subtotal += $item['total'];
            $saleDiscount += ($item['unit_price_original'] - $item['unit_price']) * $item['quantity'];
        }

        $couponResult = $this->applyCoupon($items, $subtotal, $coupon, $currency, $user);
        $couponDiscount = $couponResult['discount'];

        $taxable = max(0, $subtotal - $couponDiscount);
        $tax = $this->calculateTax($taxable);

        // With inclusive tax the advertised price already contains it, so the total is unchanged
        // and the tax figure is only broken out for the receipt.
        $total = $this->settings->tax_mode === StoreTaxMode::EXCLUSIVE->value
            ? $taxable + $tax['amount']
            : $taxable;

        $giftCardAmount = $this->giftCardCoverage($giftCard, $total, $currency);
        $amountDue = $total - $giftCardAmount;

        return [
            'currency' => $currency->code,
            'items' => $items,
            'subtotal' => $subtotal,
            'sale_discount' => $saleDiscount,
            'coupon_discount' => $couponDiscount,
            'coupon_code' => $couponDiscount > 0 ? $coupon?->code : null,
            'coupon_error' => $couponResult['error'],
            'tax_amount' => $tax['amount'],
            'tax_mode' => $this->settings->tax_mode,
            'tax_label' => $this->settings->tax_label,
            'total' => $total,
            'gift_card_amount' => $giftCardAmount,
            'amount_due' => $amountDue,
            'base_total' => $this->currencies->toBase($total, $currency),
            'base_currency' => $this->currencies->base()->code,
            'exchange_rate' => (string) ($currency->is_base ? 1 : $currency->rate_to_base),
            'formatted' => [
                'subtotal' => $this->currencies->format($subtotal, $currency),
                'sale_discount' => $this->currencies->format($saleDiscount, $currency),
                'coupon_discount' => $this->currencies->format($couponDiscount, $currency),
                'tax_amount' => $this->currencies->format($tax['amount'], $currency),
                'total' => $this->currencies->format($total, $currency),
                'gift_card_amount' => $this->currencies->format($giftCardAmount, $currency),
                'amount_due' => $this->currencies->format($amountDue, $currency),
            ],
        ];
    }

    /**
     * Price one basket line: the list price, then the package's own discount, then the best sale.
     *
     * @param  array{package: StorePackage, quantity: int, custom_price?: int|null, custom_price_currency?: string|null}  $line
     * @return array<string, mixed>
     */
    private function priceLine(array $line, StoreCurrency $currency, Collection $sales): array
    {
        /** @var StorePackage $package */
        $package = $line['package'];
        $quantity = max(1, (int) $line['quantity']);

        $list = max(0, $this->currencies->priceForPackage($package, $currency));
        $sale = null;

        if ($package->is_pay_what_you_want) {
            // The buyer set this price, so there is no list price to discount and no sale to
            // apply. The configured price is the floor.
            $unit = $original = $this->payWhatYouWantUnit($line, $package, $currency, $list);
        } else {
            $original = $list;
            $unit = max(0, $list - $package->discountFor($list));

            $sale = $this->bestSaleFor($package, $unit, $sales);
            if ($sale) {
                $unit = max(0, $unit - $sale['saving']);
            }
        }

        return [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => $quantity,
            'unit_price_original' => $original,
            'unit_price' => $unit,
            'total' => $unit * $quantity,
            'sale_name' => $sale['name'] ?? null,
            'discount_bp' => $package->is_pay_what_you_want ? 0 : (int) $package->discount_bp,
            'is_pay_what_you_want' => (bool) $package->is_pay_what_you_want,
            'formatted' => [
                'unit_price_original' => $this->currencies->format($original, $currency),
                'unit_price' => $this->currencies->format($unit, $currency),
                'total' => $this->currencies->format($unit * $quantity, $currency),
            ],
        ];
    }

    /**
     * The amount a buyer chose for a pay-what-you-want package, in the currency being quoted.
     *
     * The chosen figure is stored in whatever currency it was typed in, so quoting in another
     * currency converts it. It is always clamped up to the configured minimum and down to the
     * configured maximum, because the stored figure is user input.
     *
     * @param  array{custom_price?: int|null, custom_price_currency?: string|null}  $line
     */
    private function payWhatYouWantUnit(array $line, StorePackage $package, StoreCurrency $currency, int $minimum): int
    {
        $chosen = (int) ($line['custom_price'] ?? 0);
        $code = $line['custom_price_currency'] ?? null;

        if ($chosen <= 0 || ! $code) {
            return $minimum;
        }

        if (strtoupper($code) === $currency->code) {
            $amount = $chosen;
        } elseif ($from = $this->currencies->find($code)) {
            $amount = $this->currencies->convert($chosen, $from, $currency);
        } else {
            // The currency it was entered in is no longer enabled, so there is no rate to value it
            // with. Falling back to the minimum is the only safe reading of an amount we cannot
            // convert.
            return $minimum;
        }

        $amount = max($minimum, $amount);

        if ($package->pay_what_you_want_max) {
            $cap = max($minimum, $this->currencies->fromBase((int) $package->pay_what_you_want_max, $currency));
            $amount = min($cap, $amount);
        }

        return $amount;
    }

    /**
     * Sales never stack. When several apply, the single largest saving wins.
     *
     * @return array{name: string, saving: int}|null
     */
    private function bestSaleFor(StorePackage $package, int $unitPrice, Collection $sales): ?array
    {
        $best = null;

        foreach ($sales as $sale) {
            if (! $this->saleApplies($sale, $package)) {
                continue;
            }

            $saving = $sale->discount_type === StoreDiscountType::PERCENT
                ? intdiv($unitPrice * (int) $sale->discount_value, 10000)
                : min($unitPrice, (int) $sale->discount_value);

            if ($saving > 0 && ($best === null || $saving > $best['saving'])) {
                $best = ['name' => $sale->name, 'saving' => $saving];
            }
        }

        return $best;
    }

    private function saleApplies(StoreSale $sale, StorePackage $package): bool
    {
        $saleables = $sale->saleables;

        // No scope rows at all means the sale is store-wide.
        if ($saleables->isEmpty()) {
            return true;
        }

        foreach ($saleables as $saleable) {
            if ($saleable->saleable_type === StorePackage::class && (int) $saleable->saleable_id === $package->id) {
                return true;
            }

            if ($saleable->saleable_type === StoreCategory::class
                && $package->store_category_id !== null
                && (int) $saleable->saleable_id === $package->store_category_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, StoreSale>
     */
    private function activeSales(): Collection
    {
        return StoreSale::with('saleables')
            ->where('is_enabled', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{discount: int, error: string|null}
     */
    private function applyCoupon(array $items, int $subtotal, ?StoreCoupon $coupon, StoreCurrency $currency, ?User $user): array
    {
        if (! $coupon) {
            return ['discount' => 0, 'error' => null];
        }

        if ($error = $this->couponError($coupon, $subtotal, $user)) {
            return ['discount' => 0, 'error' => $error];
        }

        // Scoped coupons only discount the lines they actually cover.
        $eligible = $this->eligibleSubtotal($items, $coupon);

        if ($eligible <= 0) {
            return ['discount' => 0, 'error' => __('This code does not apply to anything in your cart.')];
        }

        $discount = $coupon->discount_type === StoreDiscountType::PERCENT
            ? intdiv($eligible * (int) $coupon->discount_value, 10000)
            : $this->currencies->convert(
                (int) $coupon->discount_value,
                $coupon->currency_code ? ($this->currencies->find($coupon->currency_code) ?? $this->currencies->base()) : $this->currencies->base(),
                $currency
            );

        return ['discount' => min($eligible, $discount), 'error' => null];
    }

    private function couponError(StoreCoupon $coupon, int $subtotal, ?User $user): ?string
    {
        if (! $coupon->is_enabled) {
            return __('This code is no longer active.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return __('This code is not active yet.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return __('This code has expired.');
        }

        if ($coupon->max_uses_total !== null && $coupon->used_count >= $coupon->max_uses_total) {
            return __('This code has been fully redeemed.');
        }

        if ($coupon->max_uses_per_user !== null) {
            if (! $user) {
                return __('Sign in to use this code.');
            }

            $used = $coupon->orders()
                ->where('user_id', $user->id)
                ->whereIn('status', ['paid', 'completed', 'partially_refunded'])
                ->count();

            if ($used >= $coupon->max_uses_per_user) {
                return __('You have already used this code.');
            }
        }

        if ($coupon->min_basket_amount !== null && $subtotal < $coupon->min_basket_amount) {
            return __('Your cart does not meet the minimum for this code.');
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function eligibleSubtotal(array $items, StoreCoupon $coupon): int
    {
        $couponables = $coupon->couponables;

        if ($couponables->isEmpty()) {
            return array_sum(array_column($items, 'total'));
        }

        $packageIds = $couponables->where('couponable_type', StorePackage::class)->pluck('couponable_id')->map(fn ($id) => (int) $id);
        $categoryIds = $couponables->where('couponable_type', StoreCategory::class)->pluck('couponable_id')->map(fn ($id) => (int) $id);

        $eligible = 0;
        foreach ($items as $item) {
            $package = StorePackage::find($item['package_id']);
            if (! $package) {
                continue;
            }

            if ($packageIds->contains($package->id) || ($package->store_category_id && $categoryIds->contains($package->store_category_id))) {
                $eligible += $item['total'];
            }
        }

        return $eligible;
    }

    /**
     * @return array{amount: int}
     */
    private function calculateTax(int $taxable): array
    {
        $mode = $this->settings->tax_mode;
        $rate = (int) $this->settings->tax_rate_bp;

        if ($mode === StoreTaxMode::NONE->value || $rate <= 0) {
            return ['amount' => 0];
        }

        if ($mode === StoreTaxMode::INCLUSIVE->value) {
            // Extract the tax already contained in the price rather than adding to it.
            return ['amount' => (int) round($taxable * $rate / (10000 + $rate))];
        }

        return ['amount' => intdiv($taxable * $rate, 10000)];
    }

    private function giftCardCoverage(?StoreGiftCard $giftCard, int $total, StoreCurrency $currency): int
    {
        if (! $giftCard || ! $giftCard->is_enabled || $giftCard->balance <= 0) {
            return 0;
        }

        if ($giftCard->expires_at && $giftCard->expires_at->isPast()) {
            return 0;
        }

        $balanceInCurrency = $this->currencies->convert(
            (int) $giftCard->balance,
            $this->currencies->find($giftCard->currency_code) ?? $this->currencies->base(),
            $currency
        );

        return min($balanceInCurrency, $total);
    }
}

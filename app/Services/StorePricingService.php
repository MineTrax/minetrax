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
     * @param  array<int, array{package: StorePackage, quantity: int, choices?: Collection|array}>  $lines
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
     * Price one basket line: package price plus validated option deltas, then the best sale.
     *
     * @param  array{package: StorePackage, quantity: int, choices?: Collection|array}  $line
     * @return array<string, mixed>
     */
    private function priceLine(array $line, StoreCurrency $currency, Collection $sales): array
    {
        /** @var StorePackage $package */
        $package = $line['package'];
        $quantity = max(1, (int) $line['quantity']);

        $unit = $this->currencies->priceForPackage($package, $currency);

        $chosen = [];
        foreach (collect($line['choices'] ?? []) as $choice) {
            // Deltas live in base currency and are converted, so a per-currency package override
            // does not silently re-denominate its options.
            $delta = $this->currencies->convert($choice->price_delta, $this->currencies->base(), $currency);
            $unit += $delta;

            $chosen[] = [
                'option_id' => $choice->store_package_option_id,
                'choice_id' => $choice->id,
                'placeholder' => $choice->option?->placeholder,
                'name' => $choice->name,
                'value' => $choice->value,
                'price_delta' => $delta,
            ];
        }

        // An option with a large negative delta must never make a package free or negative.
        $unit = max(0, $unit);
        $original = $unit;

        $sale = $this->bestSaleFor($package, $unit, $sales);
        if ($sale) {
            $unit = max(0, $unit - $sale['saving']);
        }

        return [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => $quantity,
            'unit_price_original' => $original,
            'unit_price' => $unit,
            'total' => $unit * $quantity,
            'sale_name' => $sale['name'] ?? null,
            'options' => $chosen,
            'formatted' => [
                'unit_price_original' => $this->currencies->format($original, $currency),
                'unit_price' => $this->currencies->format($unit, $currency),
                'total' => $this->currencies->format($unit * $quantity, $currency),
            ],
        ];
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

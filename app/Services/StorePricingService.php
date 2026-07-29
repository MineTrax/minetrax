<?php

namespace App\Services;

use App\Enums\StoreDiscountType;
use App\Enums\StorePackageGrantStatus;
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
        ?string $playerUuid = null,
    ): array {
        $currency = $currency ?? $this->currencies->resolve();
        $sales = $this->activeSales();
        $ownedInCategory = $this->ownedPricesByCategory($lines, $playerUuid, $currency);

        $items = [];
        $subtotal = 0;
        $saleDiscount = 0;
        $upgradeCredit = 0;

        foreach ($lines as $line) {
            $item = $this->priceLine($line, $currency, $sales, $ownedInCategory);
            $items[] = $item;
            $subtotal += $item['total'];
            $saleDiscount += ($item['unit_price_original'] - $item['unit_price']) * $item['quantity'];
            $upgradeCredit += $item['upgrade_credit'];
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
            'upgrade_credit' => $upgradeCredit,
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
                'upgrade_credit' => $this->currencies->format($upgradeCredit, $currency),
                'coupon_discount' => $this->currencies->format($couponDiscount, $currency),
                'tax_amount' => $this->currencies->format($tax['amount'], $currency),
                'total' => $this->currencies->format($total, $currency),
                'gift_card_amount' => $this->currencies->format($giftCardAmount, $currency),
                'amount_due' => $this->currencies->format($amountDue, $currency),
            ],
        ];
    }

    /**
     * Shop-window prices for many packages at once.
     *
     * The storefront used to work its own price out — list price minus the package's own discount —
     * which silently left sales out of every listing and package page. A buyer saw the undiscounted
     * price, added to cart, and watched it drop, because the cart quotes through this service and
     * the storefront did not.
     *
     * Takes the whole set so the active sales are loaded once rather than per package.
     *
     * @param  iterable<StorePackage>  $packages
     * @return array<int, array{price: int, price_original: int, sale_name: string|null, sale_discount_bp: int|null, sale_saving: int}>
     */
    public function listingPrices(iterable $packages, ?StoreCurrency $currency = null): array
    {
        $currency ??= $this->currencies->resolve();
        $sales = $this->activeSales();
        $prices = [];

        foreach ($packages as $package) {
            $list = max(0, $this->currencies->priceForPackage($package, $currency));

            // Pay what you want has no list price to discount: the figure shown is the floor, and
            // the buyer names the rest.
            if ($package->is_pay_what_you_want) {
                $prices[$package->id] = [
                    'price' => $list,
                    'price_original' => $list,
                    'sale_name' => null,
                    'sale_discount_bp' => null,
                    'sale_saving' => 0,
                ];

                continue;
            }

            $unit = max(0, $list - $package->discountFor($list));
            $sale = $this->bestSaleFor($package, $unit, $sales, $currency);

            $prices[$package->id] = [
                'price' => $sale ? max(0, $unit - $sale['saving']) : $unit,
                // The undiscounted price, so the card can strike it through.
                'price_original' => $list,
                'sale_name' => $sale['name'] ?? null,
                // Basis points for a percentage sale; a fixed-amount sale has no percentage to give,
                // so the badge names the amount saved instead.
                'sale_discount_bp' => ($sale['discount_type'] ?? null) === StoreDiscountType::PERCENT->value
                    ? (int) $sale['discount_value']
                    : null,
                'sale_saving' => (int) ($sale['saving'] ?? 0),
            ];
        }

        return $prices;
    }

    /**
     * Price one basket line: the list price, then the package's own discount, then the best sale.
     *
     * @param  array{package: StorePackage, quantity: int, custom_price?: int|null, custom_price_currency?: string|null}  $line
     * @return array<string, mixed>
     */
    private function priceLine(array $line, StoreCurrency $currency, Collection $sales, array $ownedInCategory = []): array
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

            $sale = $this->bestSaleFor($package, $unit, $sales, $currency);
            if ($sale) {
                $unit = max(0, $unit - $sale['saving']);
            }
        }

        // Credited once for the line, not per unit: the credit is for a package the buyer already
        // holds, and they hold it once however many they are buying now.
        $lineTotal = $unit * $quantity;
        $credit = min($lineTotal, $this->upgradeCreditFor($package, $unit, $ownedInCategory));
        $lineTotal -= $credit;

        return [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => $quantity,
            'unit_price_original' => $original,
            'unit_price' => $unit,
            'upgrade_credit' => $credit,
            'total' => $lineTotal,
            'sale_name' => $sale['name'] ?? null,
            'discount_bp' => $package->is_pay_what_you_want ? 0 : (int) $package->discount_bp,
            'is_pay_what_you_want' => (bool) $package->is_pay_what_you_want,
            'formatted' => [
                'unit_price_original' => $this->currencies->format($original, $currency),
                'unit_price' => $this->currencies->format($unit, $currency),
                'upgrade_credit' => $this->currencies->format($credit, $currency),
                'total' => $this->currencies->format($lineTotal, $currency),
            ],
        ];
    }

    /**
     * What the buyer is credited for a cheaper package they already hold in the same category.
     *
     * Only a genuine upgrade earns a credit: if what they already hold costs the same or more this
     * is a sidegrade or a downgrade, and crediting it would hand the package over for nothing.
     *
     * @param  array<int, int>  $ownedInCategory  category id => the dearest owned price, in this currency
     */
    private function upgradeCreditFor(StorePackage $package, int $unitPrice, array $ownedInCategory): int
    {
        $owned = $ownedInCategory[$package->store_category_id] ?? 0;

        return $owned > 0 && $owned < $unitPrice ? $owned : 0;
    }

    /**
     * For each cumulative category in the basket, the price of the dearest package the player
     * already holds there.
     *
     * Read from active grants, so a refund that revoked the grant also withdraws the credit. Priced
     * at today's price rather than what was paid: that is what "pay the difference" means when the
     * price has since moved, and it is the same figure the storefront is showing.
     *
     * @param  array<int, array{package: StorePackage, quantity: int}>  $lines
     * @return array<int, int>
     */
    private function ownedPricesByCategory(array $lines, ?string $playerUuid, StoreCurrency $currency): array
    {
        if (! $playerUuid) {
            return [];
        }

        $categoryIds = collect($lines)
            ->map(fn (array $line) => $line['package'])
            ->filter(fn (StorePackage $package) => $package->store_category_id
                && $package->category?->is_cumulative)
            ->pluck('store_category_id')
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return [];
        }

        $owned = StorePackage::query()
            ->whereIn('store_category_id', $categoryIds)
            ->whereHas('grants', fn ($query) => $query
                ->where('player_uuid', $playerUuid)
                ->where('status', StorePackageGrantStatus::ACTIVE)
            )
            ->with('prices')
            ->get();

        $prices = [];

        foreach ($owned as $package) {
            $price = $this->currencies->priceForPackage($package, $currency);
            $categoryId = (int) $package->store_category_id;

            $prices[$categoryId] = max($prices[$categoryId] ?? 0, $price);
        }

        return $prices;
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
    private function bestSaleFor(StorePackage $package, int $unitPrice, Collection $sales, StoreCurrency $currency): ?array
    {
        $best = null;

        foreach ($sales as $sale) {
            if (! $this->saleApplies($sale, $package)) {
                continue;
            }

            // A fixed sale amount is held in the base currency — the sale has no currency of its
            // own — so it converts before being compared against a price in the quoted currency.
            // Without this, "$5 off" would take ¥5 off a JPY price.
            $saving = $sale->discount_type === StoreDiscountType::PERCENT
                ? intdiv($unitPrice * (int) $sale->discount_value, 10000)
                : min($unitPrice, $this->currencies->fromBase((int) $sale->discount_value, $currency));

            if ($saving > 0 && ($best === null || $saving > $best['saving'])) {
                $best = [
                    'name' => $sale->name,
                    'saving' => $saving,
                    // Carried so a badge can state the sale as configured. Working the percentage
                    // back out of the saving misreports it: the saving is rounded down to whole
                    // minor units per package, so a flat 15% reads as 14.8% on one price and 14.9%
                    // on another.
                    'discount_type' => $sale->discount_type->value,
                    'discount_value' => (int) $sale->discount_value,
                ];
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

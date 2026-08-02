<?php

namespace App\Services;

use App\Enums\StoreDiscountType;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StoreSaleScope;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\StoreSaleable;
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
        private StoreTaxService $taxes,
    ) {}

    /**
     * Price a basket.
     *
     * @param  array<int, array{package: StorePackage, quantity: int, custom_price?: int|null, custom_price_currency?: string|null}>  $lines
     * @param  Collection<int, StoreCoupon>|null  $coupons  at most one exclusive, plus any stackable ones
     * @return array<string, mixed>
     */
    public function quote(
        array $lines,
        ?StoreCurrency $currency = null,
        ?Collection $coupons = null,
        ?StoreGiftCard $giftCard = null,
        ?User $user = null,
        ?string $playerUuid = null,
        ?int $countryId = null,
    ): array {
        $currency = $currency ?? $this->currencies->resolve();
        $activeSales = $this->activeSales();
        // Measured before any sale is applied, because measuring it after has no fixed point:
        // applying a 20% sale to a basket sitting exactly on a threshold drops it under, which
        // withdraws the sale, which puts it back over. Whether a minimum is met is a property of
        // the basket, so it is settled here and priceLine() never learns thresholds exist.
        $qualifying = $this->qualifyingSubtotal($lines, $currency);
        $sales = $this->salesMetBy($activeSales, $qualifying, $currency);
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

        $couponResult = $this->applyCoupons($items, $subtotal, $coupons ?? new Collection, $currency, $user);
        $couponDiscount = $couponResult['total'];

        $taxable = max(0, $subtotal - $couponDiscount);
        // Taxed on what the buyer actually pays, so a coupon reduces the tax with it. The rule is
        // chosen by the buyer's country; an unknown country falls back to the country-less rule.
        $tax = $this->taxes->calculate($taxable, $this->taxes->resolveFor($countryId));
        $total = $tax['total'];

        $giftCardAmount = $this->giftCardCoverage($giftCard, $total, $currency);
        $amountDue = $total - $giftCardAmount;

        return [
            'currency' => $currency->code,
            'items' => $items,
            'subtotal' => $subtotal,
            // What the basket is worth for the purpose of a minimum-spend condition. Reported so
            // the cart can explain a sale that has not kicked in yet.
            'qualifying_subtotal' => $qualifying,
            'unlockable_sales' => $this->unlockableSales($activeSales, $lines, $qualifying, $currency),
            'sale_discount' => $saleDiscount,
            'upgrade_credit' => $upgradeCredit,
            'coupon_discount' => $couponDiscount,
            // Every attached coupon, in the order it was applied, with what each one took off.
            // Includes the ones that took nothing — a coupon that was rejected, or that arrived at
            // an already-free basket, still has to be nameable, or the buyer reads a reason it did
            // not apply beside a field that looks empty and has no way to take it back off.
            'coupons' => array_map(
                fn (array $row) => $this->presentCoupon($row, $currency),
                $couponResult['applied'],
            ),
            // Keyed by code, so the chip that carries a rejected coupon carries its own reason.
            'coupon_errors' => $couponResult['errors'],
            // The gift card renders beside the coupons and comes off the same box, so the cart
            // needs it in the same shape rather than as a bare amount.
            'gift_card' => $giftCard ? [
                'code' => $giftCard->code,
                'amount' => $giftCardAmount,
                'amount_formatted' => $this->currencies->format($giftCardAmount, $currency),
            ] : null,
            'tax_amount' => $tax['amount'],
            // Kept for the receipt and for the order snapshot: a rate that changes next year must
            // not rewrite an order placed under the old one.
            'tax_name' => $tax['name'],
            'tax_rate_bp' => $tax['rate_bp'],
            'tax_is_inclusive' => $tax['is_inclusive'],
            // The rule's own name is the label now — "Spain's VAT" rather than a store-wide word.
            'tax_label' => $tax['name'],
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
     * @return array<int, array<string, mixed>>
     */
    public function listingPrices(iterable $packages, ?StoreCurrency $currency = null): array
    {
        $currency ??= $this->currencies->resolve();
        $activeSales = $this->activeSales();
        // A listing has no basket to measure, so a sale with a minimum cannot be priced in here:
        // the card would advertise a price the cart, quoting against the real basket, would refuse
        // to honour. salesMetBy(..., 0, ...) reads as "the sales an empty basket clears", which is
        // the honest statement of a listing's position. The card says what it would take to unlock
        // the rest instead, through the conditional_sale_* keys below.
        $sales = $this->salesMetBy($activeSales, 0, $currency);
        $conditional = $activeSales->filter(fn (StoreSale $sale) => $sale->min_basket_amount !== null);
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
                ] + $this->noConditionalSale();

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
            ] + $this->conditionalSaleFor($package, $unit, $conditional, $currency);
        }

        return $prices;
    }

    /**
     * What a shopper would have to spend to put this package on sale.
     *
     * The largest conditional sale covering it, stated as configured rather than derived: the same
     * rounding argument as bestSaleFor(). Deliberately not priced into `price` — see listingPrices().
     *
     * @param  Collection<int, StoreSale>  $conditional
     * @return array<string, mixed>
     */
    private function conditionalSaleFor(StorePackage $package, int $unitPrice, Collection $conditional, StoreCurrency $currency): array
    {
        $best = null;
        $bestSaving = 0;

        foreach ($conditional as $sale) {
            if (! $this->saleApplies($sale, $package)) {
                continue;
            }

            $saving = $this->savingFor($sale, $unitPrice, $currency);

            if ($saving > 0 && $saving > $bestSaving) {
                $best = $sale;
                $bestSaving = $saving;
            }
        }

        if (! $best) {
            return $this->noConditionalSale();
        }

        $minimum = $this->minimumFor($best, $currency);

        return [
            'conditional_sale_name' => $best->name,
            'conditional_sale_discount_bp' => $best->discount_type === StoreDiscountType::PERCENT
                ? (int) $best->discount_value
                : null,
            'conditional_sale_amount_formatted' => $best->discount_type === StoreDiscountType::PERCENT
                ? null
                : $this->currencies->format($bestSaving, $currency),
            'conditional_sale_minimum_formatted' => $this->currencies->format($minimum, $currency),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function noConditionalSale(): array
    {
        return [
            'conditional_sale_name' => null,
            'conditional_sale_discount_bp' => null,
            'conditional_sale_amount_formatted' => null,
            'conditional_sale_minimum_formatted' => null,
        ];
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
        $unit = $this->preSaleUnit($line, $package, $currency, $list);

        if ($package->is_pay_what_you_want) {
            // The buyer set this price, so there is no list price to discount and no sale to
            // apply. The configured price is the floor.
            $original = $unit;
        } else {
            $original = $list;

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
            // Which sale priced this line, so the order can record it and the sale's commands can
            // resolve at delivery. bestSaleFor() only returns a sale that saved something, so an id
            // here always means the sale actually reduced this line.
            'sale_id' => $sale['id'] ?? null,
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
     * What one unit of this line costs before any sale touches it.
     *
     * Deliberately sale-free and credit-free: this is the figure a minimum-spend condition is
     * measured against, and it is the input bestSaleFor() discounts. Extracted so the two callers
     * cannot drift apart.
     *
     * @param  array{quantity: int, custom_price?: int|null, custom_price_currency?: string|null}  $line
     */
    private function preSaleUnit(array $line, StorePackage $package, StoreCurrency $currency, int $list): int
    {
        return $package->is_pay_what_you_want
            ? $this->payWhatYouWantUnit($line, $package, $currency, $list)
            : max(0, $list - $package->discountFor($list));
    }

    /**
     * What the basket is worth for the purpose of a minimum-spend condition.
     *
     * The whole basket counts, not only the lines a given sale covers — the same split the coupon
     * rules already use, where the gate is the basket and the scope is what gets discounted.
     *
     * Pay-what-you-want lines count at the amount the buyer chose. That is real money about to be
     * charged, and payWhatYouWantUnit() is already sale-free, so including it adds no circularity.
     * Such a line still never receives a sale.
     *
     * @param  array<int, array{package: StorePackage, quantity: int}>  $lines
     */
    private function qualifyingSubtotal(array $lines, StoreCurrency $currency): int
    {
        $total = 0;

        foreach ($lines as $line) {
            /** @var StorePackage $package */
            $package = $line['package'];
            $list = max(0, $this->currencies->priceForPackage($package, $currency));

            $total += $this->preSaleUnit($line, $package, $currency, $list) * max(1, (int) $line['quantity']);
        }

        return $total;
    }

    /**
     * The sales whose minimum this basket clears. A sale with no minimum always clears.
     *
     * @param  Collection<int, StoreSale>  $sales
     * @return Collection<int, StoreSale>
     */
    private function salesMetBy(Collection $sales, int $qualifying, StoreCurrency $currency): Collection
    {
        return $sales->filter(
            fn (StoreSale $sale) => $sale->min_basket_amount === null
                || $qualifying >= $this->minimumFor($sale, $currency)
        );
    }

    /**
     * A sale's minimum in the currency being quoted.
     *
     * Held in the base currency, like a fixed discount amount, so it converts before it is compared.
     * Without this a "$20 minimum" would read as a "¥20 minimum" to a Japanese buyer.
     */
    private function minimumFor(StoreSale $sale, StoreCurrency $currency): int
    {
        return $this->currencies->fromBase((int) $sale->min_basket_amount, $currency);
    }

    /**
     * Sales the basket is close to but has not reached, so the cart can say what is missing.
     *
     * Only the ones that would actually discount something already in the basket: "spend more on
     * ranks" is noise to someone buying keys, and that copy belongs on the ranks listing instead.
     *
     * @param  Collection<int, StoreSale>  $sales
     * @param  array<int, array{package: StorePackage, quantity: int}>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function unlockableSales(Collection $sales, array $lines, int $qualifying, StoreCurrency $currency): array
    {
        $packages = array_column($lines, 'package');

        return $sales
            ->filter(fn (StoreSale $sale) => $sale->min_basket_amount !== null
                && $qualifying < $this->minimumFor($sale, $currency))
            ->filter(fn (StoreSale $sale) => collect($packages)->contains(
                fn (StorePackage $package) => ! $package->is_pay_what_you_want && $this->saleApplies($sale, $package)
            ))
            ->map(function (StoreSale $sale) use ($qualifying, $currency) {
                $minimum = $this->minimumFor($sale, $currency);
                $remaining = $minimum - $qualifying;

                return [
                    'name' => $sale->name,
                    'minimum' => $minimum,
                    'minimum_formatted' => $this->currencies->format($minimum, $currency),
                    'remaining' => $remaining,
                    'remaining_formatted' => $this->currencies->format($remaining, $currency),
                ];
            })
            ->values()
            ->all();
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
     * @return array{id: int, name: string, saving: int, discount_type: string, discount_value: int}|null
     */
    private function bestSaleFor(StorePackage $package, int $unitPrice, Collection $sales, StoreCurrency $currency): ?array
    {
        $best = null;

        foreach ($sales as $sale) {
            if (! $this->saleApplies($sale, $package)) {
                continue;
            }

            $saving = $this->savingFor($sale, $unitPrice, $currency);

            if ($saving > 0 && ($best === null || $saving > $best['saving'])) {
                $best = [
                    'id' => $sale->id,
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

    /**
     * What one sale takes off one unit.
     *
     * A fixed sale amount is held in the base currency — the sale has no currency of its own — so
     * it converts before being compared against a price in the quoted currency. Without this,
     * "$5 off" would take ¥5 off a JPY price.
     */
    private function savingFor(StoreSale $sale, int $unitPrice, StoreCurrency $currency): int
    {
        return $sale->discount_type === StoreDiscountType::PERCENT
            ? intdiv($unitPrice * (int) $sale->discount_value, 10000)
            : min($unitPrice, $this->currencies->fromBase((int) $sale->discount_value, $currency));
    }

    /**
     * Whether a sale covers a package.
     *
     * Driven by the sale's declared scope rather than by whether it happens to have scope rows. A
     * sale scoped to packages with nothing picked covers nothing, which is the safe reading: the
     * old behaviour promoted it to store-wide, so emptying the picker quietly discounted the
     * catalogue.
     *
     * A category-scoped sale matches the package's own category only. It does not walk down a
     * category tree, so a sale on a parent does not reach packages filed under its children.
     */
    private function saleApplies(StoreSale $sale, StorePackage $package): bool
    {
        return match ($sale->scope_type) {
            StoreSaleScope::ALL => true,
            StoreSaleScope::PACKAGES => $sale->saleables->contains(
                fn (StoreSaleable $saleable) => $saleable->saleable_type === StorePackage::class
                    && (int) $saleable->saleable_id === $package->id
            ),
            StoreSaleScope::CATEGORIES => $package->store_category_id !== null && $sale->saleables->contains(
                fn (StoreSaleable $saleable) => $saleable->saleable_type === StoreCategory::class
                    && (int) $saleable->saleable_id === (int) $package->store_category_id
            ),
        };
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
     * Take every attached coupon off the basket.
     *
     * Each one is measured against its own eligible lines and against the basket as it stood
     * *before* any coupon — parallel rather than compounded. Two 10% coupons take 20%, not 19%, and
     * the answer does not depend on which was typed first. Compounding would also make two coupons
     * scoped to different packages eat into each other, which is not what scoping them meant.
     *
     * Every attached coupon comes back, rejected ones included. A coupon that took nothing off is
     * still on the cart and still has to be nameable and removable — reporting only the ones that
     * worked would leave the buyer reading a rejection beside a list that does not contain it.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<int, StoreCoupon>  $coupons
     * @return array{total: int, applied: array<int, array{coupon: StoreCoupon, discount: int, error: string|null}>, errors: array<string, string>}
     */
    private function applyCoupons(array $items, int $subtotal, Collection $coupons, StoreCurrency $currency, ?User $user): array
    {
        $applied = [];
        $errors = [];
        $total = 0;

        foreach ($this->inApplicationOrder($coupons) as $coupon) {
            // Measured against the undiscounted subtotal, so a minimum-spend condition is a
            // property of the basket rather than of the order coupons happened to be applied in —
            // the same fixed-point argument as the sale thresholds in quote().
            $error = $this->couponError($coupon, $subtotal, $user);
            $discount = 0;

            // Scoped coupons only discount the lines they actually cover. Not measured at all for
            // a coupon that is already rejected — it walks the basket looking each package up.
            $eligible = $error ? 0 : $this->eligibleSubtotal($items, $coupon);

            if (! $error && $eligible <= 0) {
                $error = __('This code does not apply to anything in your cart.');
            }

            if (! $error) {
                $discount = $coupon->discount_type === StoreDiscountType::PERCENT
                    ? intdiv($eligible * (int) $coupon->discount_value, 10000)
                    : $this->currencies->convert(
                        (int) $coupon->discount_value,
                        $coupon->currency_code ? ($this->currencies->find($coupon->currency_code) ?? $this->currencies->base()) : $this->currencies->base(),
                        $currency
                    );

                // The basket floor. Trimming here rather than clamping the sum afterwards is what
                // keeps the per-coupon figures adding up to the total exactly, which is what the
                // order snapshot and the receipt both rely on. It is the one place application
                // order shows, and it only bites once the basket is already free.
                $discount = min($discount, $eligible, $subtotal - $total);
                $total += $discount;
            }

            if ($error) {
                $errors[$coupon->code] = $error;
            }

            $applied[] = ['coupon' => $coupon, 'discount' => $discount, 'error' => $error];
        }

        return ['total' => $total, 'applied' => $applied, 'errors' => $errors];
    }

    /**
     * The order coupons are taken off in: exclusive first, then stackable, by id within each.
     *
     * Deterministic on purpose. Anything else and the same basket could price one way on the cart
     * page and another at checkout, which is the one disagreement a store cannot have. Exclusive
     * goes first so that when the basket floor trims someone, it is a stackable extra that loses
     * rather than the main voucher the buyer came in with.
     *
     * @param  Collection<int, StoreCoupon>  $coupons
     * @return Collection<int, StoreCoupon>
     */
    private function inApplicationOrder(Collection $coupons): Collection
    {
        return $coupons
            ->filter()
            ->unique('id')
            ->sortBy([['is_stackable', 'asc'], ['id', 'asc']])
            ->values();
    }

    /**
     * One applied coupon, in the shape the cart and the receipt render.
     *
     * @param  array{coupon: StoreCoupon, discount: int}  $row
     * @return array<string, mixed>
     */
    private function presentCoupon(array $row, StoreCurrency $currency): array
    {
        return [
            'id' => $row['coupon']->id,
            'code' => $row['coupon']->code,
            'description' => $row['coupon']->description,
            // Carried so checkout can snapshot the coupon onto the order without reading it back,
            // and so an old order can still say "20% off" after the coupon has been re-rated.
            'discount_type' => $row['coupon']->discount_type->value,
            'discount_value' => (int) $row['coupon']->discount_value,
            'is_stackable' => (bool) $row['coupon']->is_stackable,
            'discount' => $row['discount'],
            'discount_formatted' => $this->currencies->format($row['discount'], $currency),
            // Null when the coupon applied cleanly. Carried per coupon rather than as one message
            // for the basket, so the reason sits on the chip it belongs to.
            'error' => $row['error'],
        ];
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

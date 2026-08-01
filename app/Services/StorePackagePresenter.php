<?php

namespace App\Services;

use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Utils\Helpers\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The shop-window shape of a package.
 *
 * Extracted from StoreController because more than one screen now shows a package card — the
 * storefront, a category, the "you might also like" shelf on a package page, and the cart's
 * cross-sell — and the out-of-stock rule had already been copy-pasted into StoreCartController,
 * where it could quietly drift out of step with the one the listings render.
 *
 * Prices are resolved server-side and shipped both raw and formatted, so the frontend never
 * performs money arithmetic and never sees a price it could tamper with meaningfully.
 */
class StorePackagePresenter
{
    /**
     * Below this many left, a lifetime-limited package says so on its card.
     */
    public const LOW_STOCK_THRESHOLD = 10;

    public function __construct(
        private StoreCurrencyService $currencies,
        private StorePricingService $pricing,
    ) {}

    /**
     * Packages a visitor is allowed to see in a listing.
     *
     * Featured packages come first, which is what "featured" means in a category that is otherwise
     * ordered by the admin's own sort order.
     */
    public function visibleQuery(): Builder
    {
        return StorePackage::query()
            ->with('prices')
            // Counted, not loaded: the listings only need to know whether a package has to be
            // configured on its own page before it can be added to the cart.
            ->withCount('variables')
            ->available()
            ->where('is_visible', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @param  iterable<StorePackage>  $packages
     * @param  array<int, array{key: string, name: string, description: string|null, type: string}>  $comparisonFields
     * @return Collection<int, array<string, mixed>>
     */
    public function collection(iterable $packages, StoreCurrency $currency, array $comparisonFields = []): Collection
    {
        $packages = $packages instanceof Collection ? $packages : collect($packages);

        // Priced in one pass so the active sales are loaded once for the whole listing rather than
        // once per card.
        $prices = $this->pricing->listingPrices($packages, $currency);

        return $packages
            ->map(fn (StorePackage $package) => $this->one($package, $currency, $prices[$package->id] ?? null)
                + ($comparisonFields ? ['comparison_values' => $this->comparisonValues($package, $comparisonFields)] : []))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function one(StorePackage $package, StoreCurrency $currency, ?array $priced = null): array
    {
        // Through the pricing service, which is the only thing that knows about sales. Working the
        // price out here meant a store-wide sale never reached a single card.
        $priced ??= $this->pricing->listingPrices([$package], $currency)[$package->id];

        $listPrice = $priced['price_original'];
        $price = $priced['price'];

        return [
            'id' => $package->id,
            'name' => $package->name,
            'slug' => $package->slug,
            'short_description' => $package->short_description,
            'photo_url' => $package->photo_url,
            'type' => Helper::enumKeyValue($package->type),
            'requires_login' => $package->requires_login,
            'is_featured' => $package->is_featured,
            'is_giftable' => $package->is_giftable,
            'min_quantity' => $package->min_quantity,
            'max_quantity' => $package->max_quantity,
            // Anything that has to be answered or named first cannot be added straight from a
            // listing; those link through to the package page instead.
            'needs_configuring' => (bool) $package->is_pay_what_you_want
                || (int) ($package->variables_count ?? 0) > 0,
            'expiry_duration_days' => $package->expiry_duration_days,
            'available_until' => $package->available_until,
            'is_out_of_stock' => $this->isOutOfStock($package),
            // How many of a lifetime-limited package are left, but only once the number is small
            // enough to be worth saying. "Only 3 left" sells; "Only 812 left" is noise.
            'stock_remaining' => $this->stockRemaining($package),
            'discount_bp' => (int) $package->discount_bp,
            'is_pay_what_you_want' => $package->is_pay_what_you_want,
            // For a pay-what-you-want package the price is the floor, not the amount charged.
            'price' => $price,
            'price_formatted' => $this->currencies->format($price, $currency),
            'price_original' => $listPrice,
            'price_original_formatted' => $this->currencies->format($listPrice, $currency),
            // Named so the card can say why the price is down, not just that it is. The discount is
            // reported as configured — basis points for a percentage sale, the formatted saving for
            // a fixed one — because deriving a percentage from the rounded prices misstates it.
            'sale_name' => $priced['sale_name'],
            'sale_discount_bp' => $priced['sale_discount_bp'],
            'sale_amount_formatted' => $priced['sale_discount_bp'] === null && $priced['sale_saving'] > 0
                ? $this->currencies->format($priced['sale_saving'], $currency)
                : null,
            'pay_what_you_want_max' => $package->pay_what_you_want_max
                ? $this->currencies->fromBase((int) $package->pay_what_you_want_max, $currency)
                : null,
        ];
    }

    /**
     * Only a lifetime global limit reads as "out of stock".
     *
     * A limit with a reset period is a rate limit rather than an inventory, and answering it here
     * would mean a count query per package in every listing. Checkout still enforces it.
     */
    public function isOutOfStock(StorePackage $package): bool
    {
        return $package->global_purchase_limit !== null
            && $package->global_purchase_limit_period_days === null
            && $package->sold_count >= $package->global_purchase_limit;
    }

    /**
     * How many are left, when that is a number a shopper should act on.
     *
     * Only for the same lifetime limit isOutOfStock() reads, and only under the threshold — an
     * unlimited package has no count to give, and a large one saying "947 left" reads as filler
     * rather than as scarcity.
     */
    public function stockRemaining(StorePackage $package): ?int
    {
        if ($package->global_purchase_limit === null || $package->global_purchase_limit_period_days !== null) {
            return null;
        }

        $left = max(0, (int) $package->global_purchase_limit - (int) $package->sold_count);

        return $left > 0 && $left <= self::LOW_STOCK_THRESHOLD ? $left : null;
    }

    /**
     * This package's row of comparison cells, one per field the category defines.
     *
     * Driven by the category's field list rather than by whatever the package happens to have
     * stored, so a field added after the package was saved renders as an empty cell instead of
     * shifting the column out of line.
     *
     * @param  array<int, array{key: string, name: string, description: string|null, type: string}>  $comparisonFields
     * @return array<string, mixed>
     */
    public function comparisonValues(StorePackage $package, array $comparisonFields): array
    {
        $stored = $package->comparison_values ?? [];
        $values = [];

        foreach ($comparisonFields as $field) {
            $values[$field['key']] = $stored[$field['key']] ?? null;
        }

        return $values;
    }

    /**
     * A short shelf of packages to put in front of someone who is already buying.
     *
     * Same category as the package they are looking at, because that is the set they are already
     * comparing; a package with no category falls back to the featured shelf rather than showing
     * nothing, since an empty row is worse than a generic one.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function relatedTo(StorePackage $package, StoreCurrency $currency, int $limit = 4): Collection
    {
        $related = $this->visibleQuery()
            ->whereKeyNot($package->id)
            ->when(
                $package->store_category_id,
                fn (Builder $q) => $q->where('store_category_id', $package->store_category_id),
                fn (Builder $q) => $q->where('is_featured', true),
            )
            ->limit($limit)
            ->get();

        return $this->collection($related, $currency);
    }

    /**
     * The shelf for a screen with no package to anchor to — the cart.
     *
     * Excludes whatever is already in the basket: recommending something a shopper has already
     * chosen wastes the slot and reads as the store not knowing what they are holding.
     *
     * @param  array<int, int>  $excludePackageIds
     * @return Collection<int, array<string, mixed>>
     */
    public function recommended(StoreCurrency $currency, array $excludePackageIds = [], int $limit = 4): Collection
    {
        $packages = $this->visibleQuery()
            ->whereKeyNot($excludePackageIds)
            // Best sellers first, so the shelf reflects what this community actually buys rather
            // than whatever the admin happened to sort to the top.
            ->reorder()
            ->orderByDesc('is_featured')
            ->orderByDesc('sold_count')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $this->collection($packages, $currency);
    }
}

<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
use App\Utils\Helpers\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StoreSettings $settings,
        private GeneralSettings $general,
    ) {}

    /**
     * The `&&` matters: turning the store module off must never leave `/` pointing at a page that
     * now 403s.
     */
    private function isStoreTheHomepage(): bool
    {
        return $this->general->homepage_route === 'store' && config('store.enabled');
    }

    /**
     * When the store owns `/`, this route redirects there so the storefront has one canonical
     * URL. Every existing route('store.index') link keeps working.
     */
    public function index(): Response|RedirectResponse
    {
        if ($this->isStoreTheHomepage()) {
            return redirect()->route('home', status: 301);
        }

        return $this->storefront();
    }

    /**
     * Builds the storefront page. Split out from index() so HomeController can render it at `/`
     * without bouncing through the redirect above.
     */
    public function storefront(): Response
    {
        $this->authorize('browse', StorePackage::class);

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/IndexStore', [
            'storeName' => $this->settings->store_name,
            'storeDescription' => $this->settings->store_description,
            'categories' => $this->categoryTree(),
            'packages' => $this->presentPackages($this->visiblePackages()->get(), $currency),
            'currency' => $this->currencyPayload($currency),
        ]);
    }

    public function showCategory(StoreCategory $storeCategory): Response
    {
        $this->authorize('browse', StorePackage::class);

        abort_unless($storeCategory->is_enabled, 404);

        $currency = $this->currencies->resolve();

        $packages = $this->visiblePackages()
            ->where('store_category_id', $storeCategory->id)
            ->get();

        return Inertia::render('Store/IndexStore', [
            'storeName' => $this->settings->store_name,
            'storeDescription' => $this->settings->store_description,
            'categories' => $this->categoryTree(),
            'activeCategory' => $storeCategory->only(['id', 'name', 'slug', 'description']),
            'packages' => $this->presentPackages($packages, $currency),
            'currency' => $this->currencyPayload($currency),
        ]);
    }

    public function showPackage(StorePackage $storePackage): Response
    {
        $this->authorize('browse', StorePackage::class);

        // A hidden package stays reachable by direct link, which is how "secret" packages work;
        // a disabled or out-of-window one is gone entirely.
        abort_unless($storePackage->is_available, 404);

        $storePackage->load(['category:id,name,slug', 'prices', 'requiredPackages:id,name,slug']);

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/ShowStorePackage', [
            'storePackage' => $this->presentPackage($storePackage, $currency) + [
                'description' => $storePackage->description,
                'category' => $storePackage->category?->only(['id', 'name', 'slug']),
                'required_packages_mode' => Helper::enumKeyValue($storePackage->required_packages_mode),
                'required_packages' => $storePackage->requiredPackages
                    ->map->only(['id', 'name', 'slug'])->values(),
            ],
            'currency' => $this->currencyPayload($currency),
        ]);
    }

    /**
     * Packages a visitor is allowed to see in a listing.
     *
     * Featured packages come first, which is what "featured" means in a category that is otherwise
     * ordered by the admin's own sort order.
     */
    private function visiblePackages(): Builder
    {
        return StorePackage::query()
            ->with('prices')
            ->available()
            ->where('is_visible', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function categoryTree(): \Illuminate\Support\Collection
    {
        return StoreCategory::query()
            ->where('is_enabled', true)
            ->where('is_visible', true)
            ->withCount(['packages' => fn ($q) => $q->available()->where('is_visible', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (StoreCategory $category) => [
                'id' => $category->id,
                'parent_id' => $category->parent_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'packages_count' => $category->packages_count,
                'photo_url' => $category->photo_url,
            ]);
    }

    /**
     * @param  Collection<int, StorePackage>  $packages
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function presentPackages(Collection $packages, $currency): \Illuminate\Support\Collection
    {
        return $packages->map(fn (StorePackage $package) => $this->presentPackage($package, $currency));
    }

    /**
     * Prices are resolved server-side and shipped both raw and formatted, so the frontend never
     * performs money arithmetic and never sees a price it could tamper with meaningfully.
     *
     * @return array<string, mixed>
     */
    private function presentPackage(StorePackage $package, $currency): array
    {
        $listPrice = $this->currencies->priceForPackage($package, $currency);
        $price = max(0, $listPrice - $package->discountFor($listPrice));

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
            'expiry_duration_days' => $package->expiry_duration_days,
            'available_until' => $package->available_until,
            'is_out_of_stock' => $this->isOutOfStock($package),
            'discount_bp' => (int) $package->discount_bp,
            'is_pay_what_you_want' => $package->is_pay_what_you_want,
            // For a pay-what-you-want package the price is the floor, not the amount charged.
            'price' => $price,
            'price_formatted' => $this->currencies->format($price, $currency),
            'price_original' => $listPrice,
            'price_original_formatted' => $this->currencies->format($listPrice, $currency),
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
    private function isOutOfStock(StorePackage $package): bool
    {
        return $package->global_purchase_limit !== null
            && $package->global_purchase_limit_period_days === null
            && $package->sold_count >= $package->global_purchase_limit;
    }

    /**
     * @return array<string, mixed>
     */
    private function currencyPayload($currency): array
    {
        return [
            'current' => $currency->code,
            'symbol' => $currency->symbol,
            'exponent' => $currency->exponent,
            'available' => $this->currencies->enabled()->map->only(['code', 'name', 'symbol'])->values(),
        ];
    }
}

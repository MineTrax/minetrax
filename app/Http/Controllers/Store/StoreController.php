<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
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
        // a disabled one is gone entirely.
        abort_unless($storePackage->is_enabled, 404);

        $storePackage->load(['category:id,name,slug', 'prices']);

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/ShowStorePackage', [
            'storePackage' => $this->presentPackage($storePackage, $currency) + [
                'description' => $storePackage->description,
                'category' => $storePackage->category?->only(['id', 'name', 'slug']),
            ],
            'currency' => $this->currencyPayload($currency),
        ]);
    }

    /**
     * Packages a visitor is allowed to see in a listing.
     */
    private function visiblePackages(): Builder
    {
        return StorePackage::query()
            ->with('prices')
            ->where('is_enabled', true)
            ->where('is_visible', true)
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
            ->withCount(['packages' => fn ($q) => $q->where('is_enabled', true)->where('is_visible', true)])
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
        $price = $this->currencies->priceForPackage($package, $currency);

        return [
            'id' => $package->id,
            'name' => $package->name,
            'slug' => $package->slug,
            'short_description' => $package->short_description,
            'photo_url' => $package->photo_url,
            'requires_login' => $package->requires_login,
            'min_quantity' => $package->min_quantity,
            'max_quantity' => $package->max_quantity,
            'expiry_duration_days' => $package->expiry_duration_days,
            'is_out_of_stock' => $package->stock_limit !== null && $package->sold_count >= $package->stock_limit,
            'price' => $price,
            'price_formatted' => $this->currencies->format($price, $currency),
        ];
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

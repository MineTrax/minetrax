<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Services\StoreCartService;
use App\Services\StoreCurrencyService;
use App\Services\StorePackagePresenter;
use App\Services\StoreVariableService;
use App\Services\StoreWidgetService;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
use App\Utils\Helpers\Helper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(
        private StoreCurrencyService $currencies,
        private StoreVariableService $variables,
        private StorePackagePresenter $presenter,
        private StoreCartService $carts,
        private StoreSettings $settings,
        private GeneralSettings $general,
        private StoreWidgetService $widgets,
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
    public function index(Request $request): Response|RedirectResponse
    {
        if ($this->isStoreTheHomepage()) {
            return redirect()->route('home', status: 301);
        }

        return $this->storefront($request);
    }

    /**
     * Builds the storefront page. Split out from index() so HomeController can render it at `/`
     * without bouncing through the redirect above.
     */
    public function storefront(Request $request): Response
    {
        $this->authorize('browse', StorePackage::class);

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/IndexStore', [
            'storeName' => $this->settings->store_name,
            'storeDescription' => $this->settings->store_description,
            'categories' => $this->categoryTree(),
            'packages' => $this->presenter->collection(
                $this->presenter->visibleQuery()->get(),
                $currency
            ),
            'cartTotalFormatted' => $this->cartTotal($request),
            'currency' => $this->currencyPayload($currency),
            // The storefront gets the same three boxes as the homepage, and needs them most when it
            // *is* the homepage — the goal bar is what turns a catalogue into a campaign.
            'storeWidgets' => $this->widgets->payload(),
        ]);
    }

    public function showCategory(Request $request, StoreCategory $storeCategory): Response
    {
        $this->authorize('browse', StorePackage::class);

        abort_unless($storeCategory->is_enabled, 404);

        $currency = $this->currencies->resolve();

        $packages = $this->presenter->visibleQuery()
            ->where('store_category_id', $storeCategory->id)
            ->get();

        $comparisonFields = $storeCategory->comparisonFields();

        return Inertia::render('Store/IndexStore', [
            'storeName' => $this->settings->store_name,
            'storeDescription' => $this->settings->store_description,
            'categories' => $this->categoryTree(),
            'activeCategory' => $storeCategory->only(['id', 'name', 'slug', 'description']) + [
                'display_type' => Helper::enumKeyValue($storeCategory->display_type),
                'is_cumulative' => $storeCategory->is_cumulative,
                'comparison_fields' => $comparisonFields,
            ],
            'packages' => $this->presenter->collection($packages, $currency, $comparisonFields),
            'cartTotalFormatted' => $this->cartTotal($request),
            'currency' => $this->currencyPayload($currency),
            // The same page component as the index, so it needs the same sidebar boxes or they
            // would vanish the moment a visitor clicked a category.
            'storeWidgets' => $this->widgets->payload(),
        ]);
    }

    /**
     * What the basket currently comes to, for the storefront's cart bar.
     *
     * A real quote, so the figure on the bar is the one the cart page shows rather than a
     * hand-rolled sum that would drift the moment a sale, a coupon or tax applied.
     *
     * Deliberately a prop of these two routes rather than a globally shared one: quoting means
     * loading every package in the cart with its prices and the active sales, and putting that on
     * `HandleInertiaRequests` would charge it to every page on the site — the forums, a profile,
     * the dashboard — for anyone who happens to have something in their basket. Null when the cart
     * is empty, which is also when the bar is hidden, so the common case costs one existence check.
     */
    private function cartTotal(Request $request): ?string
    {
        $cart = $this->carts->current($request, create: false);

        if (! $cart || $cart->items->isEmpty()) {
            return null;
        }

        return $this->carts->quote($cart, $request)['formatted']['total'];
    }

    public function showPackage(StorePackage $storePackage): Response
    {
        $this->authorize('browse', StorePackage::class);

        // A hidden package stays reachable by direct link, which is how "secret" packages work;
        // a disabled or out-of-window one is gone entirely.
        abort_unless($storePackage->is_available, 404);

        $storePackage->load(['category:id,name,slug', 'prices', 'requiredPackages:id,name,slug', 'variables']);

        $currency = $this->currencies->resolve();

        return Inertia::render('Store/ShowStorePackage', [
            'storePackage' => $this->presenter->one($storePackage, $currency) + [
                'description' => $storePackage->description,
                'category' => $storePackage->category?->only(['id', 'name', 'slug']),
                'required_packages_mode' => Helper::enumKeyValue($storePackage->required_packages_mode),
                'required_packages' => $storePackage->requiredPackages
                    ->map->only(['id', 'name', 'slug'])->values(),
            ],
            // FormKit field descriptors, built server-side from the package's variables. The same
            // schema shape the custom forms use, so the page renders them with FormKitSchema.
            'variableSchema' => $this->variables->schemaForPackage($storePackage),
            // A package page used to be a cul-de-sac: the only ways on were the back button and
            // the breadcrumb.
            'relatedPackages' => $this->presenter->relatedTo($storePackage, $currency),
            'currency' => $this->currencyPayload($currency),
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function categoryTree(): Collection
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

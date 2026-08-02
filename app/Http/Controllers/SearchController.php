<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCurrencyService;
use App\Services\StorePricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Searchable\ModelSearchAspect;
use Spatie\Searchable\Search;
use Spatie\Searchable\SearchResult;
use Spatie\Searchable\SearchResultCollection;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchString = $request->query('q');

        $search = (new Search)
            ->registerModel(User::class, function (ModelSearchAspect $modelSearchAspect) {
                $modelSearchAspect->addSearchableAttribute('name')->addSearchableAttribute('username')
                    ->with('country');
            })
            ->registerModel(Player::class, function (ModelSearchAspect $modelSearchAspect) {
                $modelSearchAspect->addSearchableAttribute('uuid')->addSearchableAttribute('username')
                    ->with(['country', 'rank']);
            });

        // Registered only when the module is on, so a disabled store adds neither a query nor an
        // empty "Shop" heading to every search.
        if (config('store.enabled')) {
            $search->registerModel(StorePackage::class, function (ModelSearchAspect $modelSearchAspect) {
                // available() and is_visible mirror the storefront's own listing rules. A hidden
                // package is unlisted on purpose — that is how a secret or promo package works —
                // so surfacing it here would be a way to find every one of them by guessing.
                $modelSearchAspect->addSearchableAttribute('name')->addSearchableAttribute('short_description')
                    ->available()
                    ->where('is_visible', true)
                    ->with(['prices', 'media']);
            });
        }

        $searchResults = $search->limitAspectResults(5)->search($searchString);

        $shopPrices = $this->shopPrices($searchResults);

        // Filter out the return attributes which are not required.
        $searchResults = $searchResults->map(function (SearchResult $item) use ($shopPrices) {
            if ($item->type === 'shop') {
                $item->slug = $item->searchable->slug;
                $item->photo_url = $item->searchable->photo_url;
                $item->price_formatted = $shopPrices[$item->searchable->id] ?? null;

                unset($item->searchable);

                return $item;
            }

            $item->country = ['name' => $item->searchable->country?->name,
                'iso_code' => $item->searchable->country?->iso_code,
                'photo_path' => $item->searchable->country?->photo_path,
            ];

            if ($item->type == 'players') {
                $item->rank = ['name' => $item->searchable?->rank?->name,
                    'photo_path' => $item->searchable?->rank?->photo_url,
                ];
                $item->rating = $item->searchable?->rating;
                $item->uuid = $item->searchable->uuid;
                $item->avatar_url = $item->searchable->avatar_url;
            }

            if ($item->type == 'users') {
                $item->profile_photo_url = $item->searchable->profile_photo_url;
                $item->username = $item->searchable->username;
            }

            unset($item->searchable);

            return $item;
        });

        return $searchResults->groupByType();
    }

    /**
     * Sale-aware prices for the shop rows, keyed by package id.
     *
     * Through the pricing service and in one pass, for the same reason the storefront does it that
     * way: a price worked out here would leave every sale out of the dropdown, and a shopper would
     * see one figure in search and a lower one on the page.
     *
     * @return array<int, string>
     */
    private function shopPrices(SearchResultCollection $results): array
    {
        $packages = $results
            ->filter(fn (SearchResult $result) => $result->type === 'shop')
            ->map(fn (SearchResult $result) => $result->searchable);

        if ($packages->isEmpty()) {
            return [];
        }

        $currencies = app(StoreCurrencyService::class);
        $currency = $currencies->resolve();

        return (new Collection(app(StorePricingService::class)->listingPrices($packages, $currency)))
            ->map(fn (array $priced) => $currencies->format($priced['price'], $currency))
            ->all();
    }
}

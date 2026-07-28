<?php

namespace App\Http\Controllers\Admin\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStorePackageRequest;
use App\Http\Requests\UpdateStorePackageRequest;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StorePackageController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StorePackage::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'store_category_id',
            'name',
            'slug',
            'short_description',
            'price',
            'sort_order',
            'is_visible',
            'is_enabled',
            'sold_count',
            'expiry_duration_days',
            'created_at',
            'updated_at',
        ];

        $packages = QueryBuilder::for(StorePackage::class)
            ->select($fields)
            ->with('category:id,name')
            ->withCount('commands')
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'slug', 'short_description'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        // Formatted here rather than in Vue: package prices are minor units of the base currency,
        // and dividing by 100 in the template would be wrong for JPY (0 decimals) and KWD (3).
        $base = app(StoreCurrencyService::class)->base();
        $packages->getCollection()->transform(function (StorePackage $package) use ($base) {
            $package->price_formatted = app(StoreCurrencyService::class)->format((int) $package->price, $base);

            return $package;
        });

        return Inertia::render('Admin/StorePackage/IndexStorePackage', [
            'packages' => $packages,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StorePackage::class);

        return Inertia::render('Admin/StorePackage/CreateStorePackage', $this->formData());
    }

    public function store(CreateStorePackageRequest $request)
    {
        $package = DB::transaction(function () use ($request) {
            $package = StorePackage::create($this->attributesFrom($request) + [
                'slug' => $request->slug,
                'created_by' => $request->user()->id,
            ]);

            $this->syncCommands($package, $request->input('commands', []));
            $this->syncPrices($package, $request->input('prices', []));

            return $package;
        });

        if ($request->hasFile('photo')) {
            $package->addMediaFromRequest('photo')->toMediaCollection('store-package');
        }

        return redirect()->route('admin.store.package.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store package has been created successfully')]]);
    }

    public function edit(StorePackage $storePackage): Response
    {
        $this->authorize('update', $storePackage);

        $storePackage->load(['commands.servers:id,name,hostname']);

        return Inertia::render('Admin/StorePackage/EditStorePackage', array_merge($this->formData(), [
            // Named storePackage, not package: `package` is a reserved word in the strict-mode
            // JavaScript that Vue templates compile to, so a bare {{ package.id }} fails to parse.
            'storePackage' => $storePackage,
        ]));
    }

    public function update(UpdateStorePackageRequest $request, StorePackage $storePackage)
    {
        DB::transaction(function () use ($request, $storePackage) {
            $storePackage->update($this->attributesFrom($request) + [
                'slug' => $request->slug,
                'updated_by' => $request->user()->id,
            ]);

            $this->syncCommands($storePackage, $request->input('commands', []));
            $this->syncPrices($storePackage, $request->input('prices', []));
        });

        if ($request->hasFile('photo')) {
            $storePackage->addMediaFromRequest('photo')->toMediaCollection('store-package');
        }

        return redirect()->route('admin.store.package.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store package has been updated successfully')]]);
    }

    public function destroy(StorePackage $storePackage)
    {
        $this->authorize('delete', $storePackage);

        // Soft delete. Order items snapshot the name and price, so past orders stay
        // readable, and expiry commands can still resolve for grants already issued.
        $storePackage->delete();

        return redirect()->route('admin.store.package.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store package has been deleted')]]);
    }

    /**
     * Shared props for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $base = app(StoreCurrencyService::class)->base();

        return [
            'categories' => StoreCategory::select(['id', 'name'])->orderBy('name')->get(),
            // Only servers that can actually receive a command are offerable as targets.
            'servers' => Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get(),
            // Prices are entered as decimals and stored as minor units. How many digits that
            // conversion involves is a property of the currency, not a constant: JPY has none
            // and KWD has three, so a hardcoded 100 would charge a Japanese buyer 100x.
            'baseCurrency' => [
                'code' => $base->code,
                'symbol' => $base->symbol,
                'exponent' => (int) $base->exponent,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(CreateStorePackageRequest $request): array
    {
        return [
            'name' => $request->name,
            'store_category_id' => $request->store_category_id,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'price' => $request->price,
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->is_visible,
            'is_enabled' => $request->is_enabled,
            'requires_login' => $request->requires_login,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'stock_limit' => $request->stock_limit,
            'player_purchase_limit' => $request->player_purchase_limit,
            'purchase_limit_period_days' => $request->purchase_limit_period_days,
            'expiry_duration_days' => $request->expiry_duration_days,
        ];
    }

    /**
     * Replace the per-currency price overrides. Keyed on currency code rather than row id, so a
     * currency the form no longer lists simply reverts to the converted base price.
     *
     * @param  array<int, array{currency_code: string, price: int}>  $prices
     */
    private function syncPrices(StorePackage $package, array $prices): void
    {
        $keptCodes = [];

        foreach ($prices as $price) {
            $code = strtoupper($price['currency_code']);
            $keptCodes[] = $code;

            $package->prices()->updateOrCreate(
                ['currency_code' => $code],
                ['price' => $price['price']]
            );
        }

        $package->prices()->whereNotIn('currency_code', $keptCodes)->delete();
    }

    /**
     * Reconcile the command set: update rows that carry an id, create those that do not, then
     * delete whatever the form no longer references.
     *
     * @param  array<int, array<string, mixed>>  $commands
     */
    private function syncCommands(StorePackage $package, array $commands): void
    {
        $keptIds = [];

        foreach ($commands as $index => $command) {
            $attributes = [
                'trigger' => $command['trigger'],
                'command' => $command['command'],
                'is_player_online_required' => (bool) ($command['is_player_online_required'] ?? false),
                'delay_seconds' => $command['delay_seconds'] ?? 0,
                'is_repeat_per_quantity' => (bool) ($command['is_repeat_per_quantity'] ?? false),
                'sort_order' => $command['sort_order'] ?? $index,
                // No servers picked means all of them, the same convention the account-link
                // commands use. Recording it means a server added later is included too.
                'is_run_on_all_servers' => count($command['servers'] ?? []) === 0,
            ];

            $serverIds = Arr::pluck($command['servers'] ?? [], 'id');

            if (! empty($command['id'])) {
                $existing = $package->commands()->whereKey($command['id'])->first();
                if ($existing) {
                    $existing->update($attributes);
                    $existing->servers()->sync($serverIds);
                    $keptIds[] = $existing->id;

                    continue;
                }
            }

            $created = $package->commands()->create($attributes);
            $created->servers()->sync($serverIds);
            $keptIds[] = $created->id;
        }

        $package->commands()->whereKeyNot($keptIds)->delete();
    }
}

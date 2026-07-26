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

            $package->servers()->sync($request->input('servers', []));
            $this->syncCommands($package, $request->input('commands', []));
            $this->syncOptions($package, $request->input('options', []));
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

        $storePackage->load(['commands', 'options.choices', 'servers:id']);

        return Inertia::render('Admin/StorePackage/EditStorePackage', array_merge($this->formData(), [
            // Named storePackage, not package: `package` is a reserved word in the strict-mode
            // JavaScript that Vue templates compile to, so a bare {{ package.id }} fails to parse.
            'storePackage' => $storePackage,
            'selectedServers' => $storePackage->servers->pluck('id'),
        ]));
    }

    public function update(UpdateStorePackageRequest $request, StorePackage $storePackage)
    {
        DB::transaction(function () use ($request, $storePackage) {
            $storePackage->update($this->attributesFrom($request) + [
                'slug' => $request->slug,
                'updated_by' => $request->user()->id,
            ]);

            $storePackage->servers()->sync($request->input('servers', []));
            $this->syncCommands($storePackage, $request->input('commands', []));
            $this->syncOptions($storePackage, $request->input('options', []));
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

        // Soft delete. Order items snapshot the name, price and options, so past orders stay
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
            'is_run_on_all_servers' => $request->is_run_on_all_servers,
            'is_player_online_required' => $request->is_player_online_required,
            'is_command_repeated_per_quantity' => $request->is_command_repeated_per_quantity,
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
                'is_player_online_required' => $command['is_player_online_required'] ?? null,
                'delay_seconds' => $command['delay_seconds'] ?? 0,
                'target' => $command['target'],
                'is_repeat_per_quantity' => $command['is_repeat_per_quantity'] ?? null,
                'sort_order' => $command['sort_order'] ?? $index,
            ];

            if (! empty($command['id'])) {
                $existing = $package->commands()->whereKey($command['id'])->first();
                if ($existing) {
                    $existing->update($attributes);
                    $keptIds[] = $existing->id;

                    continue;
                }
            }

            $keptIds[] = $package->commands()->create($attributes)->id;
        }

        $package->commands()->whereKeyNot($keptIds)->delete();
    }

    /**
     * Reconcile options and their choices. Choices are reconciled per option so that renaming a
     * choice does not orphan the selections already snapshotted on past order items.
     *
     * @param  array<int, array<string, mixed>>  $options
     */
    private function syncOptions(StorePackage $package, array $options): void
    {
        $keptOptionIds = [];

        foreach ($options as $index => $option) {
            $attributes = [
                'name' => $option['name'],
                'placeholder' => $option['placeholder'],
                'type' => $option['type'],
                'description' => $option['description'] ?? null,
                'is_required' => $option['is_required'],
                'sort_order' => $option['sort_order'] ?? $index,
            ];

            $model = null;
            if (! empty($option['id'])) {
                $model = $package->options()->whereKey($option['id'])->first();
                $model?->update($attributes);
            }

            $model ??= $package->options()->create($attributes);
            $keptOptionIds[] = $model->id;

            $keptChoiceIds = [];
            foreach ($option['choices'] ?? [] as $choiceIndex => $choice) {
                $choiceAttributes = [
                    'name' => $choice['name'],
                    'value' => $choice['value'],
                    'price_delta' => $choice['price_delta'],
                    'is_enabled' => $choice['is_enabled'],
                    'sort_order' => $choice['sort_order'] ?? $choiceIndex,
                ];

                if (! empty($choice['id'])) {
                    $existingChoice = $model->choices()->whereKey($choice['id'])->first();
                    if ($existingChoice) {
                        $existingChoice->update($choiceAttributes);
                        $keptChoiceIds[] = $existingChoice->id;

                        continue;
                    }
                }

                $keptChoiceIds[] = $model->choices()->create($choiceAttributes)->id;
            }

            $model->choices()->whereKeyNot($keptChoiceIds)->delete();
        }

        $package->options()->whereKeyNot($keptOptionIds)->delete();
    }
}

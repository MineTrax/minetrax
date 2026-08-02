<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreDiscountType;
use App\Enums\StoreSaleScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreSaleRequest;
use App\Http\Requests\UpdateStoreSaleRequest;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreSaleController extends Controller
{
    public function __construct(private StoreCurrencyService $currencies) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreSale::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'name',
            'discount_type',
            'discount_value',
            'scope_type',
            'min_basket_amount',
            'starts_at',
            'ends_at',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        $sales = QueryBuilder::for(StoreSale::class)
            ->select($fields)
            ->withCount(['saleables', 'commands'])
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        // Only a fixed amount is money, and money is formatted here because `discount_value` is
        // minor units. A percentage is basis points, which the frontend renders itself.
        $sales->getCollection()->transform(function (StoreSale $sale) {
            $sale->discount_formatted = $sale->discount_type === StoreDiscountType::FIXED
                ? $this->currencies->format((int) $sale->discount_value, $this->currencies->base())
                : null;
            $sale->min_basket_formatted = $sale->min_basket_amount !== null
                ? $this->currencies->format((int) $sale->min_basket_amount, $this->currencies->base())
                : null;
            // Whether the sale is discounting anything right now, which the dates alone do not say:
            // a sale can be inside its window and still switched off.
            $sale->is_running = $sale->is_enabled
                && (! $sale->starts_at || $sale->starts_at->isPast())
                && (! $sale->ends_at || $sale->ends_at->isFuture());

            return $sale;
        });

        return Inertia::render('Admin/StoreSale/IndexStoreSale', [
            'sales' => $sales,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreSale::class);

        return Inertia::render('Admin/StoreSale/CreateStoreSale', $this->formData());
    }

    public function store(CreateStoreSaleRequest $request)
    {
        DB::transaction(function () use ($request) {
            $sale = StoreSale::create($this->attributesFrom($request) + [
                'created_by' => $request->user()->id,
            ]);

            $this->syncScope($sale, $request->input('packages', []), $request->input('categories', []));
            $this->syncCommands($sale, $request->input('commands', []));
        });

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store sale has been created successfully')]]);
    }

    public function edit(StoreSale $storeSale): Response
    {
        $this->authorize('update', $storeSale);

        // deleted_at comes along so the picker can mark a retired package rather than dropping it
        // and silently widening the command to every package on the next save.
        $storeSale->load(['commands.servers:id,name,hostname', 'commands.packages:id,name,deleted_at']);

        return Inertia::render('Admin/StoreSale/EditStoreSale', array_merge($this->formData(), [
            'storeSale' => $storeSale,
            // Split by type so the form can bind one picker per morph target rather than teaching
            // Vue about polymorphic rows.
            'selectedPackages' => $this->scopeIdsFor($storeSale, StorePackage::class),
            'selectedCategories' => $this->scopeIdsFor($storeSale, StoreCategory::class),
        ]));
    }

    public function update(UpdateStoreSaleRequest $request, StoreSale $storeSale)
    {
        DB::transaction(function () use ($request, $storeSale) {
            $storeSale->update($this->attributesFrom($request) + [
                'updated_by' => $request->user()->id,
            ]);

            $this->syncScope($storeSale, $request->input('packages', []), $request->input('categories', []));
            $this->syncCommands($storeSale, $request->input('commands', []));
        });

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store sale has been updated successfully')]]);
    }

    public function destroy(StoreSale $storeSale)
    {
        $this->authorize('delete', $storeSale);

        // Soft, so a sale can be retired without breaking the orders it priced: their refund and
        // expiry commands still resolve through it months later. It stops discounting anything the
        // moment it is deleted, which is the only part an admin cares about.
        $storeSale->delete();

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store sale has been deleted')]]);
    }

    /**
     * Shared props for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $base = $this->currencies->base();

        return [
            'packages' => StorePackage::select(['id', 'name'])->orderBy('name')->get(),
            'categories' => StoreCategory::select(['id', 'name'])->orderBy('name')->get(),
            // Only servers that can actually receive a command are offerable as targets.
            'servers' => Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->orderBy('name')->get(),
            // A fixed sale amount is held in the base currency and converted when the buyer is
            // paying in another, so the form only ever needs the base exponent.
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
    private function attributesFrom(CreateStoreSaleRequest $request): array
    {
        return [
            'name' => $request->name,
            'discount_type' => StoreDiscountType::from($request->string('discount_type')->value()),
            'discount_value' => $request->integer('discount_value'),
            'scope_type' => StoreSaleScope::from($request->string('scope_type')->value()),
            'min_basket_amount' => $request->min_basket_amount,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_enabled' => $request->is_enabled,
        ];
    }

    /**
     * Replace the sale's scope, writing rows only for the mode it declares.
     *
     * A store-wide sale keeps no rows, and switching a sale from packages to categories drops the
     * package rows rather than leaving them behind to reappear if the mode is switched back — the
     * form shows one picker, so a hidden second selection would be a scope nobody could see.
     *
     * @param  array<int, int|string>  $packageIds
     * @param  array<int, int|string>  $categoryIds
     */
    private function syncScope(StoreSale $sale, array $packageIds, array $categoryIds): void
    {
        $sale->saleables()->delete();

        $ids = match ($sale->scope_type) {
            StoreSaleScope::PACKAGES => [StorePackage::class => $packageIds],
            StoreSaleScope::CATEGORIES => [StoreCategory::class => $categoryIds],
            StoreSaleScope::ALL => [],
        };

        foreach ($ids as $type => $selected) {
            foreach (collect($selected)->map(fn ($id) => (int) $id)->unique() as $id) {
                $sale->saleables()->create([
                    'saleable_type' => $type,
                    'saleable_id' => $id,
                ]);
            }
        }
    }

    /**
     * Reconcile the sale's command set: update rows that carry an id, create those that do not,
     * then delete whatever the form no longer references.
     *
     * Scoped through $sale->commands() throughout, so this can neither read, steal nor delete
     * another sale's commands or any package's — they share a table.
     *
     * @param  array<int, array<string, mixed>>  $commands
     */
    private function syncCommands(StoreSale $sale, array $commands): void
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
                // No servers picked means all of them, and no packages picked means every package
                // the sale discounts. Recording both means something added later is included too.
                'is_run_on_all_servers' => count($command['servers'] ?? []) === 0,
                'is_run_on_all_packages' => count($command['packages'] ?? []) === 0,
            ];

            $serverIds = Arr::pluck($command['servers'] ?? [], 'id');
            $packageIds = Arr::pluck($command['packages'] ?? [], 'id');

            $existing = ! empty($command['id'])
                ? $sale->commands()->whereKey($command['id'])->first()
                : null;

            if ($existing) {
                $existing->update($attributes);
                $row = $existing;
            } else {
                $row = $sale->commands()->create($attributes);
            }

            $row->servers()->sync($serverIds);
            $row->packages()->sync($packageIds);
            $keptIds[] = $row->id;
        }

        $sale->commands()->whereKeyNot($keptIds)->delete();
    }

    /**
     * @return array<int, int>
     */
    private function scopeIdsFor(StoreSale $sale, string $type): array
    {
        return $sale->saleables()
            ->where('saleable_type', $type)
            ->pluck('saleable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreDiscountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreSaleRequest;
use App\Http\Requests\UpdateStoreSaleRequest;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
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
            'starts_at',
            'ends_at',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        $sales = QueryBuilder::for(StoreSale::class)
            ->select($fields)
            ->withCount('saleables')
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
        });

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store sale has been created successfully')]]);
    }

    public function edit(StoreSale $storeSale): Response
    {
        $this->authorize('update', $storeSale);

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
        });

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store sale has been updated successfully')]]);
    }

    public function destroy(StoreSale $storeSale)
    {
        $this->authorize('delete', $storeSale);

        // Order items snapshot what they were charged, so deleting a finished sale cannot change
        // what a past order says it paid.
        $storeSale->delete();

        return redirect()->route('admin.store.sale.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store sale has been deleted permanently')]]);
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
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
            'is_enabled' => $request->is_enabled,
        ];
    }

    /**
     * Replace the sale's scope. No rows at all means it runs store-wide, so an empty selection is a
     * meaningful state rather than an incomplete one.
     *
     * @param  array<int, int|string>  $packageIds
     * @param  array<int, int|string>  $categoryIds
     */
    private function syncScope(StoreSale $sale, array $packageIds, array $categoryIds): void
    {
        $sale->saleables()->delete();

        foreach ([StorePackage::class => $packageIds, StoreCategory::class => $categoryIds] as $type => $ids) {
            foreach (collect($ids)->map(fn ($id) => (int) $id)->unique() as $id) {
                $sale->saleables()->create([
                    'saleable_type' => $type,
                    'saleable_id' => $id,
                ]);
            }
        }
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

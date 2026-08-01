<?php

namespace App\Http\Controllers\Admin\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreTaxRequest;
use App\Http\Requests\UpdateStoreTaxRequest;
use App\Models\Country;
use App\Models\StoreTax;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreTaxService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Tax rules: one rate per country, plus a fallback for everyone else.
 *
 * Every write clears the rule cache, so a corrected rate reaches the next buyer immediately rather
 * than up to five minutes later.
 */
class StoreTaxController extends Controller
{
    public function __construct(private StoreTaxService $taxes) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreTax::class);

        $perPage = min((int) request()->input('perPage', 10), 100);

        $fields = ['id', 'name', 'country_id', 'rate_bp', 'is_inclusive', 'is_enabled', 'created_at'];

        $taxes = QueryBuilder::for(StoreTax::class)
            ->with(['country:id,name,iso_code,flag', 'creator:id,username'])
            ->allowedFilters(...[
                'is_enabled',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'country.name'])),
            ])
            ->allowedSorts(...$fields)
            // The fallback first, then countries alphabetically: the rule that catches everyone
            // is the one an owner needs to find fastest.
            ->defaultSort('country_id')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/StoreTax/IndexStoreTax', [
            'taxes' => $taxes,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreTax::class);

        return Inertia::render('Admin/StoreTax/CreateStoreTax', $this->formData());
    }

    public function store(CreateStoreTaxRequest $request): RedirectResponse
    {
        StoreTax::create($request->validated() + ['created_by' => $request->user()->id]);
        $this->taxes->forgetCache();

        return redirect()->route('admin.store.tax.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully')]]);
    }

    public function edit(StoreTax $storeTax): Response
    {
        $this->authorize('update', $storeTax);

        return Inertia::render('Admin/StoreTax/EditStoreTax', $this->formData() + [
            'storeTax' => $storeTax,
        ]);
    }

    public function update(UpdateStoreTaxRequest $request, StoreTax $storeTax): RedirectResponse
    {
        $storeTax->update($request->validated() + ['updated_by' => $request->user()->id]);
        $this->taxes->forgetCache();

        return redirect()->route('admin.store.tax.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully')]]);
    }

    public function destroy(StoreTax $storeTax): RedirectResponse
    {
        $this->authorize('delete', $storeTax);

        // Orders keep their own snapshot of the rate they were charged, so deleting a rule cannot
        // rewrite history — it only stops it applying to future purchases.
        $storeTax->delete();
        $this->taxes->forgetCache();

        return redirect()->route('admin.store.tax.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully')]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'countries' => Country::orderBy('name')->get(['id', 'name', 'iso_code']),
        ];
    }
}

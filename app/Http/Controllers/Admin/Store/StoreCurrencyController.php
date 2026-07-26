<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StorePriceRounding;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreCurrencyRequest;
use App\Http\Requests\UpdateStoreCurrencyRequest;
use App\Models\Country;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Services\StoreCurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StoreCurrencyController extends Controller
{
    public function index(StoreCurrencyService $currencies): Response
    {
        $this->authorize('viewAny', StoreCurrency::class);

        return Inertia::render('Admin/StoreCurrency/IndexStoreCurrency', [
            'currencies' => StoreCurrency::orderBy('sort_order')->orderBy('code')->get(),
            'baseCurrency' => $currencies->base()->code,
            // Once an order exists its base_total was computed against the current base, so the
            // base can no longer be moved without a backfill.
            'baseIsLocked' => StoreOrder::exists(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreCurrency::class);

        return Inertia::render('Admin/StoreCurrency/CreateStoreCurrency', $this->formData());
    }

    public function store(CreateStoreCurrencyRequest $request): RedirectResponse
    {
        // The first currency becomes the base. Exactly one row must be the base; zero is not a
        // valid state, and leaving the very first one non-base strands the admin with a store
        // that has no reporting currency and no obvious way to give it one.
        $isFirst = ! StoreCurrency::exists();

        $attributes = array_merge($this->attributesFrom($request), [
            'code' => $request->code,
            'is_base' => $isFirst,
        ]);

        if ($isFirst) {
            // The base currency is its own unit and has to be usable, whatever the form said.
            $attributes['rate_to_base'] = 1;
            $attributes['is_enabled'] = true;
        }

        StoreCurrency::create($attributes);

        return redirect()->route('admin.store-currency.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Currency has been created successfully')]]);
    }

    public function edit(StoreCurrency $storeCurrency): Response
    {
        $this->authorize('update', $storeCurrency);

        return Inertia::render('Admin/StoreCurrency/EditStoreCurrency', array_merge($this->formData(), [
            'currency' => $storeCurrency,
        ]));
    }

    public function update(UpdateStoreCurrencyRequest $request, StoreCurrency $storeCurrency): RedirectResponse
    {
        $storeCurrency->update($this->attributesFrom($request) + [
            'code' => $request->code,
        ]);

        return redirect()->route('admin.store-currency.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Currency has been updated successfully')]]);
    }

    public function destroy(StoreCurrency $storeCurrency): RedirectResponse
    {
        $this->authorize('delete', $storeCurrency);

        if ($storeCurrency->is_base) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Cannot Delete'), 'body' => __('The base currency cannot be deleted.')]]);
        }

        if (StoreOrder::where('currency', $storeCurrency->code)->exists()) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Cannot Delete'), 'body' => __('Orders exist in this currency. Disable it instead.')]]);
        }

        $storeCurrency->delete();

        return redirect()->route('admin.store-currency.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Currency has been deleted')]]);
    }

    /**
     * Promote a currency to base. Only possible while no orders exist.
     */
    public function makeBase(StoreCurrency $storeCurrency): RedirectResponse
    {
        $this->authorize('update', $storeCurrency);

        if (StoreOrder::exists()) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'error', 'title' => __('Cannot Change Base'), 'body' => __('Orders already reference the current base currency.')]]);
        }

        DB::transaction(function () use ($storeCurrency) {
            StoreCurrency::where('is_base', true)->update(['is_base' => false]);
            $storeCurrency->update(['is_base' => true, 'rate_to_base' => 1, 'is_enabled' => true]);
        });

        return redirect()->route('admin.store-currency.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Base Currency Updated'), 'body' => __('Base currency is now :code', ['code' => $storeCurrency->code])]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'roundingOptions' => collect(StorePriceRounding::cases())
                ->map(fn ($case) => ['value' => $case->value, 'label' => $case->name])
                ->values(),
            'countries' => Country::select(['id', 'name', 'iso_code'])->whereNotNull('iso_code')->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(CreateStoreCurrencyRequest $request): array
    {
        return [
            'name' => $request->name,
            'symbol' => $request->symbol,
            'symbol_position' => $request->symbol_position,
            'exponent' => $request->exponent,
            'rate_to_base' => $request->rate_to_base,
            'is_enabled' => $request->is_enabled,
            'price_rounding' => $request->price_rounding,
            'country_codes' => $request->country_codes,
            'sort_order' => $request->sort_order ?? 0,
            'rate_updated_at' => now(),
        ];
    }
}

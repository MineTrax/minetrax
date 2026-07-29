<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreDiscountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreCouponRequest;
use App\Http\Requests\UpdateStoreCouponRequest;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StorePackage;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreCouponController extends Controller
{
    public function __construct(private StoreCurrencyService $currencies) {}

    public function index(): Response
    {
        $this->authorize('viewAny', StoreCoupon::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'code',
            'description',
            'discount_type',
            'discount_value',
            'currency_code',
            'min_basket_amount',
            'max_uses_total',
            'max_uses_per_user',
            'used_count',
            'starts_at',
            'expires_at',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        $coupons = QueryBuilder::for(StoreCoupon::class)
            ->select($fields)
            ->withCount('couponables')
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'code', 'description'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();

        // Only a fixed amount is money, and money is formatted here because `discount_value` is
        // minor units — dividing by 100 in the template would be wrong for JPY and KWD. A
        // percentage is basis points, which the frontend renders itself as it does elsewhere.
        $coupons->getCollection()->transform(function (StoreCoupon $coupon) {
            $coupon->discount_formatted = $coupon->discount_type === StoreDiscountType::FIXED
                ? $this->currencies->format(
                    (int) $coupon->discount_value,
                    $this->currencies->find($coupon->currency_code) ?? $this->currencies->base()
                )
                : null;

            return $coupon;
        });

        return Inertia::render('Admin/StoreCoupon/IndexStoreCoupon', [
            'coupons' => $coupons,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreCoupon::class);

        return Inertia::render('Admin/StoreCoupon/CreateStoreCoupon', $this->formData());
    }

    public function store(CreateStoreCouponRequest $request)
    {
        DB::transaction(function () use ($request) {
            $coupon = StoreCoupon::create($this->attributesFrom($request) + [
                'created_by' => $request->user()->id,
            ]);

            $this->syncScope($coupon, $request->input('packages', []), $request->input('categories', []));
        });

        return redirect()->route('admin.store.coupon.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store coupon has been created successfully')]]);
    }

    public function edit(StoreCoupon $storeCoupon): Response
    {
        $this->authorize('update', $storeCoupon);

        $storeCoupon->loadCount('orders');

        return Inertia::render('Admin/StoreCoupon/EditStoreCoupon', array_merge($this->formData(), [
            'storeCoupon' => $storeCoupon,
            // Split by type so the form can bind one picker per morph target rather than teaching
            // Vue about polymorphic rows.
            'selectedPackages' => $this->scopeIdsFor($storeCoupon, StorePackage::class),
            'selectedCategories' => $this->scopeIdsFor($storeCoupon, StoreCategory::class),
        ]));
    }

    public function update(UpdateStoreCouponRequest $request, StoreCoupon $storeCoupon)
    {
        DB::transaction(function () use ($request, $storeCoupon) {
            $storeCoupon->update($this->attributesFrom($request) + [
                'updated_by' => $request->user()->id,
            ]);

            $this->syncScope($storeCoupon, $request->input('packages', []), $request->input('categories', []));
        });

        return redirect()->route('admin.store.coupon.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store coupon has been updated successfully')]]);
    }

    public function destroy(StoreCoupon $storeCoupon)
    {
        $this->authorize('delete', $storeCoupon);

        // Orders keep `coupon_discount` and `coupon_code` as snapshots, so a redeemed coupon can be
        // deleted without making a past order unreadable. The FK is nullOnDelete for the same reason.
        $storeCoupon->delete();

        return redirect()->route('admin.store.coupon.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store coupon has been deleted permanently')]]);
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
            // A fixed discount is entered in one of the enabled currencies; percentages are
            // currency-agnostic and need none of this.
            'currencies' => $this->currencies->enabled()->map(fn ($currency) => [
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'exponent' => (int) $currency->exponent,
            ])->values(),
            // Amounts are typed as decimals and stored as minor units, and how many digits that
            // takes is a property of the currency: JPY has none, KWD has three.
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
    private function attributesFrom(CreateStoreCouponRequest $request): array
    {
        $type = StoreDiscountType::from($request->string('discount_type')->value());

        return [
            'code' => $request->code,
            'description' => $request->description,
            'discount_type' => $type,
            'discount_value' => $request->integer('discount_value'),
            // A percentage has no currency. Clearing it means switching a coupon from fixed to
            // percent cannot leave a stale code behind to be read back later.
            'currency_code' => $type === StoreDiscountType::FIXED ? $request->currency_code : null,
            'min_basket_amount' => $request->min_basket_amount,
            'max_uses_total' => $request->max_uses_total,
            'max_uses_per_user' => $request->max_uses_per_user,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'is_enabled' => $request->is_enabled,
        ];
    }

    /**
     * Replace the coupon's scope. No rows at all means it applies store-wide, so an empty selection
     * is a meaningful state rather than an incomplete one.
     *
     * @param  array<int, int|string>  $packageIds
     * @param  array<int, int|string>  $categoryIds
     */
    private function syncScope(StoreCoupon $coupon, array $packageIds, array $categoryIds): void
    {
        $coupon->couponables()->delete();

        foreach ([StorePackage::class => $packageIds, StoreCategory::class => $categoryIds] as $type => $ids) {
            foreach (collect($ids)->map(fn ($id) => (int) $id)->unique() as $id) {
                $coupon->couponables()->create([
                    'couponable_type' => $type,
                    'couponable_id' => $id,
                ]);
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private function scopeIdsFor(StoreCoupon $coupon, string $type): array
    {
        return $coupon->couponables()
            ->where('couponable_type', $type)
            ->pluck('couponable_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}

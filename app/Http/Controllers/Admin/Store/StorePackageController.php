<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStorePackageRequest;
use App\Http\Requests\UpdateStorePackageRequest;
use App\Models\Server;
use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\StoreVariable;
use App\Queries\Filters\FilterMultipleFields;
use App\Services\StoreCurrencyService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'type',
            'price',
            'discount_bp',
            'sort_order',
            'is_visible',
            'is_enabled',
            'is_featured',
            'sold_count',
            'expiry_duration_days',
            'available_from',
            'available_until',
            'created_at',
            'updated_at',
        ];

        $packages = QueryBuilder::for(StorePackage::class)
            ->select($fields)
            ->with('category:id,name')
            ->withCount('commands')
            ->allowedFilters(...[
                ...$fields,
                // Filtering by the category's name rather than its id, so the column filter reads
                // the way the column does.
                'category.name',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'slug', 'short_description'])),
            ])
            ->allowedSorts(...$fields)
            // Grouped by category first, so a category's packages sit together rather than being
            // interleaved. Within a category it follows sort_order — the order the admin arranged,
            // and the order the storefront lists them in — with id as the tiebreaker. Uncategorised
            // packages lead, since a null category sorts first.
            ->defaultSort('store_category_id', 'sort_order', 'id')
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
            // Names rather than ids, because the column filters on category.name.
            'categoryNames' => StoreCategory::orderBy('name')->pluck('name'),
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
            $this->syncRequirements($package, $request->input('required_packages', []));
            $this->syncVariables($package, $request->input('variables', []));

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

        $storePackage->load([
            'commands.servers:id,name,hostname',
            'requiredPackages:id,name',
            'variables:id,name,identifier',
        ]);

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
            $this->syncRequirements($storePackage, $request->input('required_packages', []));
            $this->syncVariables($storePackage, $request->input('variables', []));
        });

        if ($request->hasFile('photo')) {
            $storePackage->addMediaFromRequest('photo')->toMediaCollection('store-package');
        }

        return redirect()->route('admin.store.package.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store package has been updated successfully')]]);
    }

    /**
     * Copy a package, everything hanging off it included.
     *
     * A rank tier is nine-tenths the same as the tier below it — commands, variables, prerequisites,
     * per-currency prices and comparison cells — so building the next one from scratch is where
     * mistakes come from. The copy lands **disabled**: it is otherwise a second identical package on
     * the storefront the instant it is saved.
     */
    public function duplicate(StorePackage $storePackage)
    {
        // Duplicating writes a package, so it is the create permission that governs it, not update.
        $this->authorize('create', StorePackage::class);

        $storePackage->load(['commands.servers:id', 'prices', 'requiredPackages:id', 'variables:id']);

        $copy = DB::transaction(function () use ($storePackage) {
            // replicate() rather than create(getAttributes()): the raw attributes hold
            // comparison_values as a JSON string, and handing that back to a column cast to array
            // would encode it a second time.
            $copy = $storePackage->replicate(['sold_count', 'deleted_at', 'updated_by']);
            $copy->name = $storePackage->name.' '.__('(Copy)');
            $copy->slug = $this->availableSlugFrom($storePackage->slug);
            // Nothing has been sold of a package that did not exist a moment ago, and copying the
            // count would show the storefront a sold-out badge and consume purchase limits.
            $copy->sold_count = 0;
            $copy->is_enabled = false;
            $copy->created_by = request()->user()->id;
            $copy->save();

            foreach ($storePackage->commands as $command) {
                $copiedCommand = $command->replicate();
                // Only the id moves: the copy is still owned by a package, so commandable_type
                // carries over from the original untouched.
                $copiedCommand->commandable_id = $copy->id;
                $copiedCommand->save();

                // The pivot too: a command targeting three servers is not copied by copying its row.
                $copiedCommand->servers()->sync($command->servers->pluck('id')->all());
            }

            foreach ($storePackage->prices as $price) {
                $copy->prices()->create([
                    'currency_code' => $price->currency_code,
                    'price' => $price->price,
                ]);
            }

            $copy->requiredPackages()->sync($storePackage->requiredPackages->pluck('id')->all());

            $copy->variables()->sync(
                $storePackage->variables->mapWithKeys(fn ($variable) => [
                    $variable->id => ['sort_order' => $variable->pivot->sort_order],
                ])->all()
            );

            return $copy;
        });

        // Outside the transaction: the file copy touches the disk, and a rollback could not undo it.
        if ($media = $storePackage->getFirstMedia('store-package')) {
            $media->copy($copy, 'store-package', config('store.module_disk'));
        }

        // Straight to the copy's own form, which is where the admin was heading anyway — the whole
        // point is to change the few things that differ.
        return redirect()->route('admin.store.package.edit', $copy->id)
            ->with(['toast' => ['type' => 'success', 'title' => __('Duplicated Successfully'), 'body' => __('The copy is disabled until you enable it, so it is not on the storefront yet.')]]);
    }

    public function destroy(StorePackage $storePackage)
    {
        $this->authorize('delete', $storePackage);

        // Soft delete. Order items snapshot the name and price, so past orders stay
        // readable, and expiry commands can still resolve for grants already issued.
        //
        // The slug goes with it, though: it is unique across the table including trashed rows, so a
        // deleted package would otherwise sit on `diamond-rank` forever and the admin could never
        // create another one by that name. Nothing depends on it — the row keeps its `name`, orders
        // snapshot `package_name`, and route binding cannot resolve a trashed package anyway.
        $storePackage->update(['slug' => 'deleted-'.Str::uuid()]);

        $storePackage->delete();

        return redirect()->route('admin.store.package.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store package has been deleted')]]);
    }

    /**
     * A slug near the original's that nothing else holds.
     *
     * Checked against trashed rows too, because the unique index covers them — a retired package
     * squatting on `gold-rank-copy` would otherwise fail the insert.
     */
    private function availableSlugFrom(string $slug): string
    {
        $candidate = Str::limit($slug, 200, '').'-copy';
        $suffix = 1;

        while (StorePackage::withTrashed()->where('slug', $candidate)->exists()) {
            $suffix++;
            $candidate = Str::limit($slug, 200, '').'-copy-'.$suffix;
        }

        return $candidate;
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
            // display_type and comparison_fields come along so the form can show the right
            // comparison cells the moment a category is picked, without another round trip.
            'categories' => StoreCategory::select(['id', 'name', 'display_type', 'comparison_fields'])
                ->orderBy('name')
                ->get(),
            // Only servers that can actually receive a command are offerable as targets.
            'servers' => Server::select(['id', 'name', 'hostname'])->whereNotNull('webquery_port')->get(),
            // Candidates for the "requires" picker. A package can gate on a disabled one, which is
            // how a prerequisite is retired without breaking the packages that reference it.
            'packages' => StorePackage::select(['id', 'name'])->orderBy('name')->get(),
            // Variables that can be attached, each carrying the placeholder to paste into a command.
            'variables' => StoreVariable::enabled()
                ->select(['id', 'name', 'identifier'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
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
        $type = StorePackageType::from($request->string('type')->value());

        return [
            'name' => $request->name,
            'store_category_id' => $request->store_category_id,
            'short_description' => $request->short_description,
            'description' => $request->description,
            'type' => $type,
            'price' => $request->price,
            'discount_bp' => $request->integer('discount_bp'),
            'is_pay_what_you_want' => $request->boolean('is_pay_what_you_want'),
            'pay_what_you_want_max' => $request->boolean('is_pay_what_you_want') ? $request->pay_what_you_want_max : null,
            // Cleared for a package that no longer sells credit, so switching the type back does
            // not silently reinstate an old amount.
            'gift_card_amount' => $type->issuesGiftCard() ? $request->gift_card_amount : null,
            'is_gift_card_amount_same_as_price' => $type->issuesGiftCard()
                && $request->boolean('is_gift_card_amount_same_as_price'),
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->is_visible,
            'is_enabled' => $request->is_enabled,
            'requires_login' => $request->requires_login,
            'is_featured' => $request->is_featured,
            'is_giftable' => $request->is_giftable,
            'min_quantity' => $request->min_quantity,
            'max_quantity' => $request->max_quantity,
            'player_purchase_limit' => $request->player_purchase_limit,
            'player_purchase_limit_period_days' => $request->player_purchase_limit_period_days,
            'global_purchase_limit' => $request->global_purchase_limit,
            'global_purchase_limit_period_days' => $request->global_purchase_limit_period_days,
            'expiry_duration_days' => $request->expiry_duration_days,
            'available_from' => $request->available_from,
            'available_until' => $request->available_until,
            'required_packages_mode' => StorePackageRequirementMode::from(
                $request->string('required_packages_mode')->value()
            ),
            'comparison_values' => $this->comparisonValuesFrom($request),
        ];
    }

    /**
     * The package's comparison cells, narrowed to the fields its category actually defines.
     *
     * Filtering here rather than storing whatever arrived keeps a stale cell from a previous
     * category out of the row, and stops a crafted payload parking arbitrary keys on the record.
     *
     * @return array<string, mixed>|null
     */
    private function comparisonValuesFrom(CreateStorePackageRequest $request): ?array
    {
        $submitted = $request->input('comparison_values');

        if (! is_array($submitted) || ! $request->store_category_id) {
            return null;
        }

        $category = StoreCategory::find($request->store_category_id);
        $keys = collect($category?->comparison_fields ?? [])->pluck('key')->filter()->all();

        $values = collect($submitted)
            ->only($keys)
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();

        return $values ?: null;
    }

    /**
     * Replace the prerequisite list. The pivot carries nothing but the pair, so a plain sync is
     * the whole operation.
     *
     * @param  array<int, int|string>  $packageIds
     */
    private function syncRequirements(StorePackage $package, array $packageIds): void
    {
        $package->requiredPackages()->sync(
            collect($packageIds)->map(fn ($id) => (int) $id)->reject(fn (int $id) => $id === $package->id)->unique()->all()
        );
    }

    /**
     * Replace the attached variables, keeping the order they were arranged in — that order is what
     * the buyer sees the inputs in.
     *
     * @param  array<int, int|string>  $variableIds
     */
    private function syncVariables(StorePackage $package, array $variableIds): void
    {
        $package->variables()->sync(
            collect($variableIds)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->mapWithKeys(fn (int $id, int $index) => [$id => ['sort_order' => $index]])
                ->all()
        );
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

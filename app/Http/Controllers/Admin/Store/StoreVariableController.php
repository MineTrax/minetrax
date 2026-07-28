<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreVariableType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreVariableRequest;
use App\Http\Requests\UpdateStoreVariableRequest;
use App\Models\StoreVariable;
use App\Queries\Filters\FilterMultipleFields;
use App\Utils\Helpers\Helper;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreVariableController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StoreVariable::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'name',
            'identifier',
            'type',
            'is_required',
            'is_enabled',
            'sort_order',
            'created_at',
            'updated_at',
        ];

        $variables = QueryBuilder::for(StoreVariable::class)
            ->select($fields)
            ->withCount('packages')
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'identifier'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/StoreVariable/IndexStoreVariable', [
            'variables' => $variables,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreVariable::class);

        return Inertia::render('Admin/StoreVariable/CreateStoreVariable', $this->formData());
    }

    public function store(CreateStoreVariableRequest $request)
    {
        StoreVariable::create($this->attributesFrom($request) + [
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.store.variable.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store variable has been created successfully')]]);
    }

    public function edit(StoreVariable $storeVariable): Response
    {
        $this->authorize('update', $storeVariable);

        return Inertia::render('Admin/StoreVariable/EditStoreVariable', array_merge($this->formData(), [
            'storeVariable' => $storeVariable->loadCount('packages'),
        ]));
    }

    public function update(UpdateStoreVariableRequest $request, StoreVariable $storeVariable)
    {
        $storeVariable->update($this->attributesFrom($request) + [
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.store.variable.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store variable has been updated successfully')]]);
    }

    public function destroy(StoreVariable $storeVariable)
    {
        $this->authorize('delete', $storeVariable);

        // The pivot cascades, so the variable simply stops being asked for. Orders already placed
        // keep their own snapshot of the values, so nothing already sold becomes unreadable.
        $storeVariable->delete();

        return redirect()->route('admin.store.variable.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store variable has been deleted permanently')]]);
    }

    /**
     * Shared props for the create and edit forms.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'variableTypes' => collect(StoreVariableType::cases())
                ->map(fn (StoreVariableType $type) => Helper::enumKeyValue($type) + [
                    'has_options' => $type->hasOptions(),
                    'is_free_text' => $type->isFreeText(),
                ])
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(CreateStoreVariableRequest $request): array
    {
        $type = StoreVariableType::from($request->string('type')->value());

        return [
            'name' => $request->name,
            'identifier' => $request->identifier,
            'description' => $request->description,
            'type' => $type,
            // Cleared when the type no longer uses them, so switching back does not resurrect a
            // stale choice list.
            'options' => $type->hasOptions() ? $request->options : null,
            'max_length' => $type->isFreeText() ? $request->max_length : null,
            'placeholder' => $request->placeholder,
            'is_required' => $request->is_required,
            'is_enabled' => $request->is_enabled,
            'sort_order' => $request->sort_order ?? 0,
        ];
    }
}

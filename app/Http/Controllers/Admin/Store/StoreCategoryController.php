<?php

namespace App\Http\Controllers\Admin\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreCategoryRequest;
use App\Http\Requests\UpdateStoreCategoryRequest;
use App\Models\StoreCategory;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StoreCategoryController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', StoreCategory::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'parent_id',
            'name',
            'slug',
            'description',
            'sort_order',
            'is_visible',
            'is_enabled',
            'created_at',
            'updated_at',
        ];

        $categories = QueryBuilder::for(StoreCategory::class)
            ->select($fields)
            ->with('parent:id,name')
            ->withCount('packages')
            ->allowedFilters(...[
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'slug', 'description'])),
            ])
            ->allowedSorts(...$fields)
            ->defaultSort('sort_order')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/StoreCategory/IndexStoreCategory', [
            'categories' => $categories,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', StoreCategory::class);

        return Inertia::render('Admin/StoreCategory/CreateStoreCategory', [
            'parentCategories' => StoreCategory::select(['id', 'name'])->orderBy('name')->get(),
        ]);
    }

    public function store(CreateStoreCategoryRequest $request)
    {
        $category = StoreCategory::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->is_visible,
            'is_enabled' => $request->is_enabled,
            'created_by' => $request->user()->id,
        ]);

        if ($request->hasFile('photo')) {
            $category->addMediaFromRequest('photo')->toMediaCollection('store-category');
        }

        return redirect()->route('admin.store.category.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Store category has been created successfully')]]);
    }

    public function edit(StoreCategory $storeCategory): Response
    {
        $this->authorize('update', $storeCategory);

        return Inertia::render('Admin/StoreCategory/EditStoreCategory', [
            'category' => $storeCategory,
            'parentCategories' => StoreCategory::select(['id', 'name'])
                ->whereKeyNot($storeCategory->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateStoreCategoryRequest $request, StoreCategory $storeCategory)
    {
        $storeCategory->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->is_visible,
            'is_enabled' => $request->is_enabled,
            'updated_by' => $request->user()->id,
        ]);

        if ($request->hasFile('photo')) {
            $storeCategory->addMediaFromRequest('photo')->toMediaCollection('store-category');
        }

        return redirect()->route('admin.store.category.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Store category has been updated successfully')]]);
    }

    public function destroy(StoreCategory $storeCategory)
    {
        $this->authorize('delete', $storeCategory);

        // Packages and child categories are detached rather than removed: the FKs are
        // nullOnDelete, so nothing is silently destroyed along with the category.
        $storeCategory->delete();

        return redirect()->route('admin.store.category.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Store category has been deleted permanently')]]);
    }
}

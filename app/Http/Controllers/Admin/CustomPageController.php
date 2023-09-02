<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCustomPageRequest;
use App\Http\Requests\UpdateCustomPageRequest;
use App\Models\CustomPage;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomPageController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CustomPage::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $customPages = QueryBuilder::for(CustomPage::class)
            ->allowedFilters([
                'id',
                'title',
                'path',
                'is_in_navbar',
                'is_visible',
                'is_redirect',
                'redirect_url',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'is_sidebar_visible',
                'is_open_in_new_tab',
                'is_html_page',
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'title', 'path', 'redirect_url'])),
            ])
            ->allowedSorts(['id', 'title', 'path', 'is_in_navbar', 'is_visible', 'is_redirect', 'redirect_url', 'created_by', 'updated_by', 'created_at', 'updated_at', 'is_sidebar_visible', 'is_html_page', 'is_open_in_new_tab'])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/CustomPage/IndexCustomPage', [
            'customPages' => $customPages,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', CustomPage::class);

        return Inertia::render('Admin/CustomPage/CreateCustomPage');
    }

    public function store(CreateCustomPageRequest $request)
    {
        $customPage = CustomPage::create([
            'title' => $request->title,
            'path' => $request->path,
            'body' => $request->body,
            'is_visible' => $request->is_visible,
            'is_in_navbar' => $request->is_in_navbar,
            'is_redirect' => $request->is_redirect,
            'is_sidebar_visible' => $request->is_sidebar_visible,
            'is_open_in_new_tab' => $request->is_open_in_new_tab,
            'is_html_page' => $request->is_html_page,
            'redirect_url' => $request->redirect_url,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.custom-page.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Custom Page is created successfully')]]);
    }

    public function edit(CustomPage $customPage)
    {
        $this->authorize('update', $customPage);

        return Inertia::render('Admin/CustomPage/EditCustomPage', [
            'customPage' => $customPage,
        ]);
    }

    public function update(UpdateCustomPageRequest $request, CustomPage $customPage)
    {
        $this->authorize('update', $customPage);

        $customPage->title = $request->title;
        $customPage->body = $request->body;
        $customPage->path = $request->path;
        $customPage->is_visible = $request->is_visible;
        $customPage->is_in_navbar = $request->is_in_navbar;
        $customPage->is_sidebar_visible = $request->is_sidebar_visible;
        $customPage->is_open_in_new_tab = $request->is_open_in_new_tab;
        $customPage->is_html_page = $request->is_html_page;
        $customPage->is_redirect = $request->is_redirect;
        $customPage->redirect_url = $request->redirect_url;
        $customPage->updated_by = $request->user()->id;
        $customPage->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Custom Page updated successfully')]]);
    }

    public function destroy(CustomPage $customPage)
    {
        $this->authorize('delete', $customPage);

        $customPage->delete();

        return redirect()->route('admin.custom-page.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Custom Page has been deleted permanently')]]);
    }
}

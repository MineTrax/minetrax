<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateDownloadRequest;
use App\Http\Requests\UpdateDownloadRequest;
use App\Models\Download;
use App\Queries\Filters\FilterMultipleFields;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DownloadController extends Controller
{
    public function index(): \Inertia\Response
    {
        $this->authorize('viewAny', Download::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'name',
            'slug',
            'description',
            'is_external',
            'is_external_url_hidden',
            'is_only_auth',
            'is_active',
            'file_name',
            'file_url',
            'min_role_weight_required',
            'download_count',
            'created_at',
            'updated_at',
        ];

        $downloads = QueryBuilder::for(Download::class)
            ->select($fields)
            ->with('media')
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['id', 'name', 'description', 'file_name'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-id')
            ->paginate($perPage)
            ->through(function ($download) {
                return $download->append('file')->makeHidden('media');
            })
            ->withQueryString();

        return Inertia::render('Admin/Download/IndexDownload', [
            'downloads' => $downloads,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function create(): \Inertia\Response
    {
        $this->authorize('create', Download::class);

        return Inertia::render('Admin/Download/CreateDownload');
    }

    public function store(CreateDownloadRequest $request)
    {
        $isExternal = $request->is_external;
        if ($isExternal) {
            $fileUrl = $request->file_url;
        }

        $download = Download::create([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'is_external' => $request->is_external,
            'is_external_url_hidden' => $request->is_external_url_hidden,
            'file_url' => $fileUrl ?? null,
            'file_name' => $request->file_name ?? null,
            'is_only_auth' => $request->is_only_auth,
            'min_role_weight_required' => $request->min_role_weight_required,
            'is_active' => $request->is_active,
            'created_by' => $request->user()->id,
        ]);

        // Upload the File if !isExternal
        if (! $isExternal && $request->hasFile('file')) {
            $download->addMediaFromRequest('file')->toMediaCollection('download');
        }

        return redirect()->route('admin.download.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Created Successfully'), 'body' => __('Download has been created successfully')]]);
    }

    public function edit(Download $download): \Inertia\Response
    {
        $this->authorize('update', Download::class);

        return Inertia::render('Admin/Download/EditDownload', [
            'download' => $download->append('file')->makeHidden('media'),
        ]);
    }

    public function update(UpdateDownloadRequest $request, Download $download)
    {
        $this->authorize('update', $download);

        $download->update([
            'name' => $request->name,
            'slug' => \Str::slug($request->name),
            'description' => $request->description,
            'is_external_url_hidden' => $request->is_external_url_hidden,
            'file_name' => $request->file_name ?? null,
            'is_only_auth' => $request->is_only_auth,
            'min_role_weight_required' => $request->min_role_weight_required,
            'is_active' => $request->is_active,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.download.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Updated Successfully'), 'body' => __('Download has been updated successfully')]]);
    }

    public function destroy(Download $download)
    {
        $this->authorize('delete', $download);

        $download->delete();

        return redirect()->route('admin.download.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted Successfully'), 'body' => __('Download has been deleted permanently')]]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DownloadController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $isAuthenticated = (bool) $request->user();

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $fields = [
            'id',
            'name',
            'slug',
            'download_count',
            'created_at',
            'updated_at',
        ];
        $downloads = QueryBuilder::for(Download::class)
            ->where('is_active', true)
            ->when(! $isAuthenticated, function ($query) {
                $query->where('is_only_auth', false);
            })
            ->when($isAuthenticated, function ($query) use ($request) {
                $user = $request->user();
                $userRoleWeight = $user->roles->max('weight');
                $query->where(function ($q) use ($userRoleWeight) {
                    $q->where('min_role_weight_required', '<=', $userRoleWeight)->orWhere('min_role_weight_required', null);
                });
            })
            ->select($fields)
            ->allowedFilters([
                ...$fields,
                AllowedFilter::custom('q', new FilterMultipleFields(['name', 'description'])),
            ])
            ->allowedSorts($fields)
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Download/IndexDownload', [
            'downloads' => $downloads,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function show(Request $request, Download $download)
    {
        $this->authorize('download', $download);

        return Inertia::render('Download/ShowDownload', [
            'download' => $download->append(['description_html'])->only([
                'id',
                'name',
                'slug',
                'description',
                'description_html',
                'is_active',
                'download_count',
                'created_at',
                'updated_at',
            ]),
        ]);
    }

    public function download(Download $download)
    {
        $this->authorize('download', $download);

        $download->increment('download_count');

        $isExternal = $download->is_external;
        $isExternalUrlHidden = $download->is_external_url_hidden;

        if (! $isExternal) {
            $file = $download->file;

            return response()->download($file->getPath(), $download->file_name);
        }

        if ($isExternal && $isExternalUrlHidden) {
            return response()->streamDownload(function () use ($download) {
                echo file_get_contents($download->file_url);
            }, $download->file_name);
        }

        return redirect($download->file_url);
    }
}

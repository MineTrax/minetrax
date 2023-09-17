<?php

namespace App\Http\Controllers;

use App\Models\Download;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DownloadController extends Controller
{
    public function index(Request $request)
    {
        $isAuthenticated = $request->user() ? true : false;

        return Inertia::render('Download/IndexDownload', [
            'downloads' => $downloads,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function show(Request $request, Download $download)
    {
        $this->authorize('download', $download);

        return Inertia::render('Download/ShowDownload', [
            'download' => $download,
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

            return $file;
        }

        if ($isExternal && $isExternalUrlHidden) {
            return response()->streamDownload(function () use ($download) {
                echo file_get_contents($download->file_url);
            }, $download->file_name);
        }

        return redirect($download->file_url);
    }
}

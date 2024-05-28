<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FailedJob;
use App\Queries\Filters\FilterMultipleFields;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FailedJobController extends Controller
{
    //
    public function index()
    {
        $this->authorize('viewAny', FailedJob::class);

        $perPage = request()->input('perPage', 10);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $failedJobs = QueryBuilder::for(FailedJob::class)
            ->allowedFilters([
                'id',
                'uuid',
                'connection',
                'queue',
                'payload',
                'exception',
                AllowedFilter::custom('q', new FilterMultipleFields([
                    'id',
                    'uuid',
                    'connection',
                    'queue',
                    'payload',
                    'exception',
                ])),
            ])
            ->allowedSorts(['id', 'uuid', 'connection', 'queue', 'exception', 'failed_at'])
            ->defaultSort('-failed_at')
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Admin/FailedJob/IndexFailedJob', [
            'jobs' => $failedJobs,
            'filters' => request()->all(['perPage', 'sort', 'filter']),
        ]);
    }

    public function retry(Request $request)
    {
        $this->authorize('retry', FailedJob::class);

        // run command to retry the job
        if ($request->uuid) {
            try {
                Artisan::call('queue:retry '.$request->uuid);
            } catch (ModelNotFoundException $e) {
                Artisan::call('queue:forget '.$request->uuid);
            }
        } else {
            try {
                Artisan::call('queue:retry all');
            } catch (ModelNotFoundException $e) {
                Artisan::call('queue:flush');
            }
        }

        return redirect()->route('admin.failed-job.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Retry Queued!'), 'body' => __('Job has been queued for retrying!')]]);
    }

    public function destroy(Request $request)
    {
        $this->authorize('delete', FailedJob::class);

        if ($request->uuid) {
            Artisan::call('queue:forget '.$request->uuid);
        } else {
            Artisan::call('queue:flush');
        }

        return redirect()->route('admin.failed-job.index')
            ->with(['toast' => ['type' => 'success', 'title' => __('Deleted!')]]);
    }
}

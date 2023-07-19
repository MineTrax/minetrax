<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\TruncateIntelDataJob;
use App\Jobs\TruncatePlayerStatsJob;
use App\Jobs\TruncateServerChatlogsJob;
use App\Jobs\TruncateServerConsolelogsJob;
use App\Jobs\TruncateShoutsJob;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Log;

class DangerSettingController extends Controller
{
    public function __construct()
    {
        // Only Super Admin can access Dangerzone.
        $this->middleware(['role:' . Role::SUPER_ADMIN_ROLE_NAME]);
    }

    public function show(): \Inertia\Response
    {
        return Inertia::render('Admin/Setting/DangerSetting');
    }

    public function truncateShouts(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_SHOUTS', [
            'causer' => $request->user()->username,
        ]);
        TruncateShoutsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All shouts will be deleted shortly.')]]);
    }

    public function truncateConsolelogs(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_CONSOLELOGS', [
            'causer' => $request->user()->username,
        ]);
        TruncateServerConsolelogsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All consolelogs will be deleted shortly.')]]);
    }

    public function truncateChatlogs(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_CHATLOGS', [
            'causer' => $request->user()->username,
        ]);
        TruncateServerChatlogsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All chat history will be deleted shortly.')]]);
    }

    public function truncatePlayerStats(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_STATS', [
            'causer' => $request->user()->username,
        ]);
        TruncatePlayerStatsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All player stats will be deleted shortly.')]]);
    }

    public function truncateIntelData(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_INTEL', [
            'causer' => $request->user()->username,
        ]);
        TruncateIntelDataJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => __('Queued Successfully! Analytics data will be deleted shortly. It may take upto 1 hour to complete.')]]);
    }
}
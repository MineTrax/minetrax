<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Jobs\ResetPlayerIntelStatsJob;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Jobs\TruncatePlayerIntelJob;
use App\Jobs\TruncatePlayerPunishmentJob;
use App\Jobs\TruncateServerChatlogsJob;
use App\Jobs\TruncateServerConsolelogsJob;
use App\Jobs\TruncateServerIntelJob;
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
        return Inertia::render('Admin/Setting/DangerSetting', [
            'inProgressList' => [
                'truncateShouts' => Cache::get('dangerzone::truncate_shouts'),
                'truncateConsolelogs' => Cache::get('dangerzone::truncate_consolelogs'),
                'truncateChatlogs' => Cache::get('dangerzone::truncate_chatlogs'),
                'truncatePlayerIntelData' => Cache::get('dangerzone::truncate_player_intel_data'),
                'truncateServerIntelData' => Cache::get('dangerzone::truncate_server_intel_data'),
                'truncatePlayerPunishments' => Cache::get('dangerzone::truncate_player_punishments'),
                'resetPlayerIntelStats' => Cache::get('dangerzone::reset_player_intel_stats'),
            ]
        ]);
    }

    public function truncateShouts(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_SHOUTS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_shouts', now(), 3600 * 24);
        TruncateShoutsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All shouts will be deleted shortly.')]]);
    }

    public function truncateConsolelogs(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_CONSOLELOGS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_consolelogs', now(), 3600 * 24);
        TruncateServerConsolelogsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All consolelogs will be deleted shortly.')]]);
    }

    public function truncateChatlogs(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_CHATLOGS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_chatlogs', now(), 3600 * 24);
        TruncateServerChatlogsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All chat history will be deleted shortly.')]]);
    }

    public function truncatePlayerIntelData(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_PLAYER_INTEL', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_player_intel_data', now(), 3600 * 24);
        TruncatePlayerIntelJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! All player stats will be deleted shortly. It may take upto 1 minute to complete.')]]);
    }

    public function truncateServerIntelData(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_SERVER_INTEL', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_server_intel_data', now(), 3600 * 24);
        TruncateServerIntelJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => __('Queued Successfully! Server Analytics data will be deleted shortly. It may take upto 1 minute to complete.')]]);
    }

    public function truncatePlayerPunishments(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('TRUNCATE_PLAYER_PUNISHMENTS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::truncate_player_punishments', now(), 3600 * 24);
        TruncatePlayerPunishmentJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => __('Queued Successfully! Player Punishments data will be deleted shortly. It may take upto few minute to complete.')]]);
    }

    public function resetPlayerIntelStats(Request $request): \Illuminate\Http\RedirectResponse
    {
        Log::alert('RESET_PLAYER_INTEL_STATS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::reset_player_intel_stats', now(), 3600 * 24);
        ResetPlayerIntelStatsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => __('Queued Successfully! Player Intel stats will be reset shortly. It may take upto few minute to complete.')]]);
    }
}

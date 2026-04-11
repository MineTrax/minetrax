<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\ResetPlayerIntelStatsJob;
use App\Jobs\TruncatePlayerIntelJob;
use App\Jobs\TruncatePlayerPunishmentJob;
use App\Jobs\TruncateServerChatlogsJob;
use App\Jobs\TruncateServerConsolelogsJob;
use App\Jobs\TruncateServerIntelJob;
use App\Jobs\TruncateShoutsJob;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Log;

class DangerSettingController extends Controller
{
    public function __construct()
    {
        // Only Super Admin can access Dangerzone.
        $this->middleware(['role:'.Role::SUPER_ADMIN_ROLE_NAME]);
    }

    public function show(): Response
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
            ],
        ]);
    }

    public function truncateShouts(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_SHOUTS', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_shouts', now(), 3600 * 24);
        TruncateShoutsJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Shouts before :date will be deleted shortly.', ['date' => $beforeDate])
            : __('Queued Successfully! All shouts will be deleted shortly.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => $message]]);
    }

    public function truncateConsolelogs(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_CONSOLELOGS', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_consolelogs', now(), 3600 * 24);
        TruncateServerConsolelogsJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Console logs before :date will be deleted shortly.', ['date' => $beforeDate])
            : __('Queued Successfully! All consolelogs will be deleted shortly.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => $message]]);
    }

    public function truncateChatlogs(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_CHATLOGS', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_chatlogs', now(), 3600 * 24);
        TruncateServerChatlogsJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Chat history before :date will be deleted shortly.', ['date' => $beforeDate])
            : __('Queued Successfully! All chat history will be deleted shortly.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => $message]]);
    }

    public function truncatePlayerIntelData(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_PLAYER_INTEL', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_player_intel_data', now(), 3600 * 24);
        TruncatePlayerIntelJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Player intel data before :date will be deleted shortly. It may take upto 1 minute to complete.', ['date' => $beforeDate])
            : __('Queued Successfully! All player stats will be deleted shortly. It may take upto 1 minute to complete.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => $message]]);
    }

    public function truncateServerIntelData(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_SERVER_INTEL', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_server_intel_data', now(), 3600 * 24);
        TruncateServerIntelJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Server Analytics data before :date will be deleted shortly. It may take upto 1 minute to complete.', ['date' => $beforeDate])
            : __('Queued Successfully! Server Analytics data will be deleted shortly. It may take upto 1 minute to complete.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => $message]]);
    }

    public function truncatePlayerPunishments(Request $request): RedirectResponse
    {
        $request->validate(['before_date' => ['nullable', 'date']]);
        $beforeDate = $request->input('before_date');

        Log::alert('TRUNCATE_PLAYER_PUNISHMENTS', [
            'causer' => $request->user()->username,
            'before_date' => $beforeDate,
        ]);

        Cache::put('dangerzone::truncate_player_punishments', now(), 3600 * 24);
        TruncatePlayerPunishmentJob::dispatch($beforeDate);

        $message = $beforeDate
            ? __('Queued Successfully! Player Punishments before :date will be deleted shortly. It may take upto few minute to complete.', ['date' => $beforeDate])
            : __('Queued Successfully! Player Punishments data will be deleted shortly. It may take upto few minute to complete.');

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => $message]]);
    }

    public function resetPlayerIntelStats(Request $request): RedirectResponse
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

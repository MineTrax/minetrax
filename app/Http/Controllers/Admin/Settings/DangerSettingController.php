<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Jobs\ResetPlayerIntelStatsJob;
use App\Jobs\RunDatabaseBackupJob;
use App\Jobs\TruncatePlayerIntelJob;
use App\Jobs\TruncatePlayerPunishmentJob;
use App\Jobs\TruncateServerChatlogsJob;
use App\Jobs\TruncateServerConsolelogsJob;
use App\Jobs\TruncateServerIntelJob;
use App\Jobs\TruncateShoutsJob;
use App\Models\BackupRun;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'hasRecentBackup' => $this->hasRecentBackup(),
            'backupRuns' => BackupRun::query()
                ->latest('started_at')
                ->limit(20)
                ->get(),
        ]);
    }

    public function exportDatabaseBackup(Request $request): RedirectResponse
    {
        Log::alert('EXPORT_DATABASE_BACKUP', [
            'causer' => $request->user()->username,
        ]);

        $backupRun = BackupRun::create([
            'triggered_by_user_id' => $request->user()->id,
            'started_at' => now(),
            'status' => 'pending',
        ]);

        RunDatabaseBackupJob::dispatch($backupRun);

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 7000, 'title' => __('Queued Successfully! Database backup is being created and will appear in the list shortly.')]]);
    }

    public function downloadBackup(Request $request, BackupRun $backupRun): StreamedResponse
    {
        if ($backupRun->status !== 'completed' || ! $backupRun->file_path) {
            abort(404);
        }

        $disk = Storage::disk('danger-backups');

        if (! $disk->exists($backupRun->file_path)) {
            abort(404);
        }

        return $disk->download($backupRun->file_path, $backupRun->filename);
    }

    public function deleteBackup(Request $request, BackupRun $backupRun): RedirectResponse
    {
        Log::alert('DELETE_DATABASE_BACKUP', [
            'causer' => $request->user()->username,
            'backup_run_id' => $backupRun->id,
            'filename' => $backupRun->filename,
        ]);

        if ($backupRun->file_path && Storage::disk('danger-backups')->exists($backupRun->file_path)) {
            Storage::disk('danger-backups')->delete($backupRun->file_path);
        }

        $backupRun->delete();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 5000, 'title' => __('Backup deleted successfully.')]]);
    }

    protected function hasRecentBackup(): bool
    {
        return BackupRun::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subHours(24))
            ->exists();
    }

    protected function assertRecentBackup(): ?RedirectResponse
    {
        if (! $this->hasRecentBackup()) {
            return redirect()->back()
                ->with(['toast' => ['type' => 'warning', 'milliseconds' => 10000, 'title' => __('A database backup created within the last 24 hours is required before running this action. Please create a backup first.')]]);
        }

        return null;
    }

    public function truncateShouts(Request $request): RedirectResponse
    {
        if ($redirect = $this->assertRecentBackup()) {
            return $redirect;
        }

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
        if ($redirect = $this->assertRecentBackup()) {
            return $redirect;
        }

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
        if ($redirect = $this->assertRecentBackup()) {
            return $redirect;
        }

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
        if ($redirect = $this->assertRecentBackup()) {
            return $redirect;
        }

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
        if ($redirect = $this->assertRecentBackup()) {
            return $redirect;
        }

        Log::alert('RESET_PLAYER_INTEL_STATS', [
            'causer' => $request->user()->username,
        ]);

        Cache::put('dangerzone::reset_player_intel_stats', now(), 3600 * 24);
        ResetPlayerIntelStatsJob::dispatch();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'milliseconds' => 10000, 'title' => __('Queued Successfully! Player Intel stats will be reset shortly. It may take upto few minute to complete.')]]);
    }
}

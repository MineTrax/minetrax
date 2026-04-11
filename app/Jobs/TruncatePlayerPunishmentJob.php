<?php

namespace App\Jobs;

use App\Models\PlayerPunishment;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TruncatePlayerPunishmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?string $beforeDate = null)
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[TruncatePlayerPunishmentJob] Starting job...', ['before_date' => $this->beforeDate]);
        Cache::put('dangerzone::truncate_player_punishments', now(), 3600 * 24);

        $query = PlayerPunishment::query();
        if ($this->beforeDate) {
            $query->where('created_at', '<', $this->beforeDate);
        }

        $query->lazyById()->each(function ($punishment) {
            $punishment->delete();
        });

        Cache::forget('dangerzone::truncate_player_punishments');
        Log::info('[TruncatePlayerPunishmentJob] Job completed successfully');
    }
}

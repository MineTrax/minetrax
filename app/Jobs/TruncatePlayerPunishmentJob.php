<?php

namespace App\Jobs;

use App\Models\PlayerPunishment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;

class TruncatePlayerPunishmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::put('dangerzone::truncate_player_punishments', now(), 3600 * 24);

        PlayerPunishment::lazyById()->each(function ($punishment) {
            $punishment->delete();
        });

        Cache::forget('dangerzone::truncate_player_punishments');
    }
}

<?php

namespace App\Jobs;

use App\Models\MinecraftPlayer;
use App\Models\MinecraftPlayerDeath;
use App\Models\MinecraftPlayerEvent;
use App\Models\MinecraftPlayerMobKill;
use App\Models\MinecraftPlayerPvpKill;
use App\Models\MinecraftPlayerSession;
use App\Models\MinecraftPlayerWorldStat;
use App\Models\Player;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Cache;
use Illuminate\Support\Facades\Log;

class TruncatePlayerIntelJob implements ShouldQueue
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
        Log::info('[TruncatePlayerIntelJob] Starting job...');
        Cache::put('dangerzone::truncate_player_intel_data', now(), 3600 * 24);

        // Delete player intel.
        MinecraftPlayerDeath::query()->delete();
        MinecraftPlayerEvent::query()->delete();
        MinecraftPlayerMobKill::query()->delete();
        MinecraftPlayerPvpKill::query()->delete();
        MinecraftPlayerWorldStat::query()->delete();
        MinecraftPlayerSession::query()->delete();
        MinecraftPlayer::query()->delete();
        Player::query()->delete();

        Cache::forget('dangerzone::truncate_player_intel_data');
        Log::info('[TruncatePlayerIntelJob] Job completed successfully');
    }
}

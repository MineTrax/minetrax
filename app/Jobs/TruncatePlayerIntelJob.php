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
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TruncatePlayerIntelJob implements ShouldQueue
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
        Log::info('[TruncatePlayerIntelJob] Starting job...', ['before_date' => $this->beforeDate]);
        Cache::put('dangerzone::truncate_player_intel_data', now(), 3600 * 24);

        if ($this->beforeDate) {
            // Only delete time-series data before the date, keep player records intact.
            MinecraftPlayerDeath::where('created_at', '<', $this->beforeDate)->delete();
            MinecraftPlayerEvent::where('created_at', '<', $this->beforeDate)->delete();
            MinecraftPlayerMobKill::where('created_at', '<', $this->beforeDate)->delete();
            MinecraftPlayerPvpKill::where('created_at', '<', $this->beforeDate)->delete();
            MinecraftPlayerWorldStat::where('created_at', '<', $this->beforeDate)->delete();
            MinecraftPlayerSession::where('created_at', '<', $this->beforeDate)->delete();
        } else {
            // Delete everything including player records.
            MinecraftPlayerDeath::query()->delete();
            MinecraftPlayerEvent::query()->delete();
            MinecraftPlayerMobKill::query()->delete();
            MinecraftPlayerPvpKill::query()->delete();
            MinecraftPlayerWorldStat::query()->delete();
            MinecraftPlayerSession::query()->delete();
            MinecraftPlayer::query()->delete();
            Player::query()->delete();
        }

        Cache::forget('dangerzone::truncate_player_intel_data');
        Log::info('[TruncatePlayerIntelJob] Job completed successfully');
    }
}

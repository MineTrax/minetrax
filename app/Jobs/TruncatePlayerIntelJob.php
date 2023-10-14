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

class TruncatePlayerIntelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            // Delete player intel.
            MinecraftPlayerDeath::query()->delete();
            MinecraftPlayerEvent::query()->delete();
            MinecraftPlayerMobKill::query()->delete();
            MinecraftPlayerPvpKill::query()->delete();
            MinecraftPlayerWorldStat::query()->delete();
            MinecraftPlayerSession::query()->delete();
            MinecraftPlayer::query()->delete();
            Player::query()->delete();
        }, 3);
    }
}

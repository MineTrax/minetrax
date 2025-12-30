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
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ResetPlayerIntelStatsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int | null $serverId = null)
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('[ResetPlayerIntelStatsJob] Starting job...');
        Cache::put('dangerzone::reset_player_intel_stats', now(), 3600 * 24);

        DB::transaction(function () {
            // Delete player intel stats.
            MinecraftPlayerDeath::query()->delete();
            MinecraftPlayerEvent::query()->delete();
            MinecraftPlayerMobKill::query()->delete();
            MinecraftPlayerPvpKill::query()->delete();
            MinecraftPlayerWorldStat::query()->delete();
            MinecraftPlayerSession::query()->delete();

            // Reset player stats.
            MinecraftPlayer::query()->update([
                'mob_kills' => 0,
                'player_kills' => 0,
                'deaths' => 0,
                'afk_time' => 0,
                'play_time' => 0,
                'ban_counter' => 0,
                'kick_counter' => 0,
                'items_used' => 0,
                'items_mined' => 0,
                'items_picked_up' => 0,
                'items_dropped' => 0,
                'items_broken' => 0,
                'items_crafted' => 0,
                'items_placed' => 0,
                'items_consumed' => 0,
                'fish_caught' => 0,
                'items_enchanted' => 0,
                'times_slept_in_bed' => 0,
                'times_jumped' => 0,
                'raids_won' => 0,
                'distance_traveled' => 0,
                'distance_traveled_on_land' => 0,
                'distance_traveled_on_water' => 0,
                'distance_traveled_on_air' => 0,
                'pvp_damage_given' => 0,
                'pvp_damage_taken' => 0,
                'vault_balance' => 0,
                'vault_groups' => null,
            ]);
            Player::query()->update([
                'rating' => 0,
                'total_score' => 0,
                'position' => null,
                'total_used' => 0,
                'total_mined' => 0,
                'total_picked_up' => 0,
                'total_dropped' => 0,
                'total_broken' => 0,
                'total_crafted' => 0,
                'total_mob_kills' => 0,
                'total_player_kills' => 0,
                'total_deaths' => 0,
                'total_fish_caught' => 0,
                'total_sleep_in_bed' => 0,
                'total_jumps' => 0,
                'total_leave_game' => 0,
                'total_money' => null,
                'total_tems_placed' => 0,
                'total_tems_consumed' => 0,
                'total_tems_enchanted' => 0,
                'raids_won' => 0,
                'play_time' => 0,
                'afk_time' => 0,
                'distance_traveled' => 0,
                'distance_traveled_on_land' => 0,
                'distance_traveled_on_water' => 0,
                'distance_traveled_on_air' => 0,
                'pvp_damage_given' => 0,
                'pvp_damage_taken' => 0,
            ]);
        }, 3);

        Cache::forget('dangerzone::reset_player_intel_stats');
        Log::info('[ResetPlayerIntelStatsJob] Job completed successfully');
    }
}

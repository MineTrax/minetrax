<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResyncPlayersTableJob implements ShouldQueue
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
     *
     * Intentionally not wrapped in a single transaction: the resync can take minutes and other
     * writers (score/rating jobs, live player intel) update the players table meanwhile, which makes
     * MariaDB's snapshot isolation fail with "Record has changed since last read" (error 1020).
     * Every update below recomputes full totals, so a partial run is safe to simply re-run.
     */
    public function handle(): void
    {
        $rawPlayerList = DB::table('minecraft_players')
            ->select('player_uuid')
            ->selectRaw('SUM(mob_kills) as mob_kills')
            ->selectRaw('SUM(player_kills) as player_kills')
            ->selectRaw('SUM(deaths) as deaths')
            ->selectRaw('SUM(afk_time) as afk_time')
            ->selectRaw('SUM(play_time) as play_time')
            ->selectRaw('SUM(items_used) as items_used')
            ->selectRaw('SUM(items_mined) as items_mined')
            ->selectRaw('SUM(items_picked_up) as items_picked_up')
            ->selectRaw('SUM(items_dropped) as items_dropped')
            ->selectRaw('SUM(items_broken) as items_broken')
            ->selectRaw('SUM(items_crafted) as items_crafted')
            ->selectRaw('SUM(items_placed) as items_placed')
            ->selectRaw('SUM(items_consumed) as items_consumed')
            ->selectRaw('SUM(fish_caught) as fish_caught')
            ->selectRaw('SUM(items_enchanted) as items_enchanted')
            ->selectRaw('SUM(times_slept_in_bed) as times_slept_in_bed')
            ->selectRaw('SUM(times_jumped) as times_jumped')
            ->selectRaw('SUM(raids_won) as raids_won')
            ->selectRaw('SUM(distance_traveled) as distance_traveled')
            ->selectRaw('SUM(distance_traveled_on_land) as distance_traveled_on_land')
            ->selectRaw('SUM(distance_traveled_on_water) as distance_traveled_on_water')
            ->selectRaw('SUM(distance_traveled_on_air) as distance_traveled_on_air')
            ->selectRaw('SUM(pvp_damage_given) as pvp_damage_given')
            ->selectRaw('SUM(pvp_damage_taken) as pvp_damage_taken')
            ->selectRaw('SUM(vault_balance) as vault_balance')
            ->selectRaw('MIN(first_seen_at) as first_seen_at')
            ->selectRaw('MAX(last_seen_at) as last_seen_at')
            ->groupBy('player_uuid')
            ->lazyById(1000, 'player_uuid');

        foreach ($rawPlayerList as $rawPlayer) {
            // Get latest minecraft_player.
            $latestMinecraftPlayer = DB::table('minecraft_players')
                ->where('player_uuid', $rawPlayer->player_uuid)
                ->orderBy('last_seen_at', 'DESC')
                ->first();

            // Count how many sessions user have.
            $sessionCount = DB::table('minecraft_player_sessions')
                ->where('player_uuid', $rawPlayer->player_uuid)
                ->count();

            DB::table('players')->where('uuid', $rawPlayer->player_uuid)->update([
                'uuid' => $rawPlayer->player_uuid,
                'username' => $latestMinecraftPlayer->player_username,
                'total_used' => $rawPlayer->items_used,
                'total_mined' => $rawPlayer->items_mined,
                'total_picked_up' => $rawPlayer->items_picked_up,
                'total_dropped' => $rawPlayer->items_dropped,
                'total_broken' => $rawPlayer->items_broken,
                'total_crafted' => $rawPlayer->items_crafted,
                'total_items_placed' => $rawPlayer->items_placed,
                'total_items_consumed' => $rawPlayer->items_consumed,
                'total_mob_kills' => $rawPlayer->mob_kills,
                'total_player_kills' => $rawPlayer->player_kills,
                'total_deaths' => $rawPlayer->deaths,
                'total_fish_caught' => $rawPlayer->fish_caught,
                'total_items_enchanted' => $rawPlayer->items_enchanted,
                'total_sleep_in_bed' => $rawPlayer->times_slept_in_bed,
                'total_jumps' => $rawPlayer->times_jumped,
                'total_leave_game' => $sessionCount,
                'ip_address' => $latestMinecraftPlayer->player_ip_address,
                'total_money' => $latestMinecraftPlayer->vault_balance,
                'country_id' => $latestMinecraftPlayer->country_id,
                'raids_won' => $rawPlayer->raids_won,
                'play_time' => $rawPlayer->play_time,
                'afk_time' => $rawPlayer->afk_time,
                'distance_traveled' => $rawPlayer->distance_traveled,
                'distance_traveled_on_land' => $rawPlayer->distance_traveled_on_land,
                'distance_traveled_on_water' => $rawPlayer->distance_traveled_on_water,
                'distance_traveled_on_air' => $rawPlayer->distance_traveled_on_air,
                'pvp_damage_given' => $rawPlayer->pvp_damage_given,
                'pvp_damage_taken' => $rawPlayer->pvp_damage_taken,
                'last_minecraft_version' => $latestMinecraftPlayer->last_minecraft_version,
                'last_join_address' => $latestMinecraftPlayer->last_join_address,
                'first_seen_at' => $rawPlayer->first_seen_at,
                'last_seen_at' => $rawPlayer->last_seen_at,
                'created_at' => $rawPlayer->first_seen_at,
                'updated_at' => $rawPlayer->last_seen_at,
            ]);
        }

        // Delete players which no longer have any minecraft_players record on any server.
        DB::table('players')
            ->whereNotExists(function (Builder $query) {
                $query->selectRaw('1')
                    ->from('minecraft_players')
                    ->whereColumn('minecraft_players.player_uuid', 'players.uuid');
            })
            ->delete();
    }
}

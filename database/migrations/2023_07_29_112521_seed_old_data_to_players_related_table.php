<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->seedMinecraftPlayersTable();
        $this->seedPlayersTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('minecraft_players')->delete();
    }


    private function seedMinecraftPlayersTable()
    {
        // TRUNCATE minecraft_players table (so if in some case someone run it 2 times its don't break).
        DB::table('minecraft_players')->delete();

        // DISTINCT playerlist from minecraft_player_sessions table.
        $rawMinecraftPlayerList = DB::table('minecraft_player_sessions')
            ->select('player_uuid', 'server_id')
            ->selectRaw('MAX(minecraft_version) as minecraft_version')
            ->selectRaw('SUM(play_time) as play_time')
            ->selectRaw('SUM(afk_time) as afk_time')
            ->selectRaw('SUM(is_banned = 1) as ban_counter')
            ->selectRaw('SUM(is_kicked = 1) as kick_counter')
            ->selectRaw('MAX(country_id) as country_id')
            ->selectRaw('MIN(session_started_at) as first_seen_at')
            ->selectRaw('MAX(session_ended_at) as last_seen_at')
            ->groupBy('player_uuid', 'server_id')
            ->orderBy('player_uuid', 'ASC')
            ->lazy();

        foreach ($rawMinecraftPlayerList as $rawMinecraftPlayer) {
            // Get aggr data from minecraft_player_events table for this player uuid and server id.
            $aggrPlayerData = DB::table('minecraft_player_events')
                ->whereIn('session_id', function (Builder $query) use ($rawMinecraftPlayer) {
                    $query->select('id')
                        ->from('minecraft_player_sessions')
                        ->where('player_uuid', $rawMinecraftPlayer->player_uuid)
                        ->where('server_id', $rawMinecraftPlayer->server_id);
                })
                ->selectRaw('SUM(mob_kills) as mob_kills')
                ->selectRaw('SUM(player_kills) as player_kills')
                ->selectRaw('SUM(deaths) as deaths')
                ->selectRaw('SUM(afk_time) as afk_time')
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
                ->selectRaw('SUM(fish_caught) as fish_caught')
                ->first();

            $lastPlayerSession = DB::table('minecraft_player_sessions')
                ->where('player_uuid', $rawMinecraftPlayer->player_uuid)
                ->where('server_id', $rawMinecraftPlayer->server_id)
                ->orderBy('id', 'DESC')
                ->first();

            // create new minecraft_player row.
            $mcPlayerId = DB::table('minecraft_players')->insertGetId(
                [
                    'server_id' => $rawMinecraftPlayer->server_id,
                    'player_uuid' => $rawMinecraftPlayer->player_uuid,
                    'player_username' => $lastPlayerSession->player_username,
                    'player_displayname' => $lastPlayerSession->player_displayname,
                    'player_ip_address' => $lastPlayerSession->player_ip_address,

                    'mob_kills' => $aggrPlayerData->mob_kills ?? 0,
                    'player_kills' => $aggrPlayerData->player_kills ?? 0,
                    'deaths' => $aggrPlayerData->deaths ?? 0,
                    'afk_time' => $aggrPlayerData->afk_time ?? 0,
                    'play_time' => $rawMinecraftPlayer->play_time ?? 0,
                    'ban_counter' => $rawMinecraftPlayer->ban_counter ?? 0,
                    'kick_counter' => $rawMinecraftPlayer->kick_counter ?? 0,
                    'items_used' => $aggrPlayerData->items_used ?? 0,
                    'items_mined' => $aggrPlayerData->items_mined ?? 0,
                    'items_picked_up' => $aggrPlayerData->items_picked_up ?? 0,
                    'items_dropped' => $aggrPlayerData->items_dropped ?? 0,
                    'items_broken' => $aggrPlayerData->items_broken ?? 0,
                    'items_crafted' => $aggrPlayerData->items_crafted ?? 0,
                    'items_placed' => $aggrPlayerData->items_placed ?? 0,
                    'items_consumed' => $aggrPlayerData->items_consumed ?? 0,
                    'fish_caught' => $aggrPlayerData->fish_caught ?? 0,
                    'items_enchanted' => $aggrPlayerData->items_enchanted ?? 0,
                    'times_slept_in_bed' => $aggrPlayerData->times_slept_in_bed ?? 0,
                    'times_jumped' => $aggrPlayerData->times_jumped ?? 0,
                    'raids_won' => $aggrPlayerData->raids_won ?? 0,
                    'distance_traveled' => $aggrPlayerData->distance_traveled ?? 0,
                    'distance_traveled_on_land' => $aggrPlayerData->distance_traveled_on_land ?? 0,
                    'distance_traveled_on_water' => $aggrPlayerData->distance_traveled_on_water ?? 0,
                    'distance_traveled_on_air' => $aggrPlayerData->distance_traveled_on_air ?? 0,
                    'pvp_damage_given' => $aggrPlayerData->pvp_damage_given ?? 0,
                    'pvp_damage_taken' => $aggrPlayerData->pvp_damage_taken ?? 0,

                    'last_minecraft_version' => $lastPlayerSession->minecraft_version,
                    'last_join_address' => $lastPlayerSession->join_address,
                    'vault_balance' => $lastPlayerSession->vault_balance,
                    'vault_groups' => $lastPlayerSession->vault_groups,
                    'country_id' => $lastPlayerSession->country_id,
                    'first_seen_at' => $rawMinecraftPlayer->first_seen_at,
                    'last_seen_at' => $rawMinecraftPlayer->last_seen_at,
                    'created_at' => $rawMinecraftPlayer->first_seen_at,
                    'updated_at' => $rawMinecraftPlayer->last_seen_at,
                ]
            );

            // Update all minecraft_player_sessions with minecraft_player_id
            DB::table('minecraft_player_sessions')
                ->where('player_uuid', $rawMinecraftPlayer->player_uuid)
                ->where('server_id', $rawMinecraftPlayer->server_id)
                ->update(['minecraft_player_id' => $mcPlayerId]);
        }
    }

    function seedPlayersTable()
    {
        DB::table('players')->delete();

        // get all minecraft_players distict by player_uuid and aggr
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
            ->orderBy('player_uuid', 'ASC')
            ->lazy();

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

            $playerId = DB::table('players')->insertGetId([
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

            // Update minecraft_players table with player_id.
            DB::table('minecraft_players')
                ->where('player_uuid', $rawPlayer->player_uuid)
                ->update(['player_id' => $playerId]);
        }
    }
};

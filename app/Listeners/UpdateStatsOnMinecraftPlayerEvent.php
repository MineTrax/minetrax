<?php

namespace App\Listeners;

use App\Events\MinecraftPlayerEventCreated;
use App\Models\MinecraftPlayer;
use App\Models\Player;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateStatsOnMinecraftPlayerEvent implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MinecraftPlayerEventCreated $event): void
    {
        $minecraftPlayerEvent = $event->minecraftPlayerEvent;
        $serverId = $event->serverId;
        $rawRequest = $event->rawRequest;
        $sessionEndedAt = array_key_exists('session_ended_at', $rawRequest) && $rawRequest['session_ended_at'] ? Carbon::createFromTimestampMs($rawRequest['session_ended_at']) : null;
        $banCountIncrement = array_key_exists('is_banned', $rawRequest) && $rawRequest['is_banned'] ? 1 : 0;
        $kickCountIncrement = array_key_exists('is_kicked', $rawRequest) && $rawRequest['is_kicked'] ? 1 : 0;

        // Check if minecraft_player created. (if it exists then `player` exists for sure so we dont need to check that)
        $exists = MinecraftPlayer::where('player_uuid', $minecraftPlayerEvent->player_uuid)
            ->where('server_id', $serverId)
            ->exists();
        if (! $exists) {
            return;
        }

        DB::transaction(function () use ($minecraftPlayerEvent, $sessionEndedAt, $serverId, $banCountIncrement, $kickCountIncrement) {
            // Update minecraft_player.
            MinecraftPlayer::where('player_uuid', $minecraftPlayerEvent->player_uuid)->where('server_id', '=', $serverId)
                ->incrementEach([
                    'mob_kills' => $minecraftPlayerEvent->mob_kills,
                    'player_kills' => $minecraftPlayerEvent->player_kills,
                    'deaths' => $minecraftPlayerEvent->deaths,
                    'afk_time' => $minecraftPlayerEvent->afk_time,
                    'play_time' => $minecraftPlayerEvent->play_time,
                    'items_used' => $minecraftPlayerEvent->items_used,
                    'items_mined' => $minecraftPlayerEvent->items_mined,
                    'items_picked_up' => $minecraftPlayerEvent->items_picked_up,
                    'items_dropped' => $minecraftPlayerEvent->items_dropped,
                    'items_broken' => $minecraftPlayerEvent->items_broken,
                    'items_crafted' => $minecraftPlayerEvent->items_crafted,
                    'items_placed' => $minecraftPlayerEvent->items_placed,
                    'items_consumed' => $minecraftPlayerEvent->items_consumed,
                    'fish_caught' => $minecraftPlayerEvent->fish_caught,
                    'items_enchanted' => $minecraftPlayerEvent->items_enchanted,
                    'times_slept_in_bed' => $minecraftPlayerEvent->times_slept_in_bed,
                    'times_jumped' => $minecraftPlayerEvent->times_jumped,
                    'raids_won' => $minecraftPlayerEvent->raids_won,
                    'distance_traveled' => $minecraftPlayerEvent->distance_traveled,
                    'distance_traveled_on_land' => $minecraftPlayerEvent->distance_traveled_on_land,
                    'distance_traveled_on_water' => $minecraftPlayerEvent->distance_traveled_on_water,
                    'distance_traveled_on_air' => $minecraftPlayerEvent->distance_traveled_on_air,
                    'pvp_damage_given' => $minecraftPlayerEvent->pvp_damage_given,
                    'pvp_damage_taken' => $minecraftPlayerEvent->pvp_damage_taken,
                    'ban_counter' => $banCountIncrement,
                    'kick_counter' => $kickCountIncrement,
                ], [
                    'player_displayname' => $minecraftPlayerEvent->player_displayname,
                    'player_username' => $minecraftPlayerEvent->player_username,
                    'player_ip_address' => $minecraftPlayerEvent->ip_address,
                    'vault_balance' => $minecraftPlayerEvent->vault_balance,
                    'vault_groups' => $minecraftPlayerEvent->vault_groups,
                    'last_seen_at' => $sessionEndedAt ?? now(),
                ]);

            // Update player
            Player::where('uuid', $minecraftPlayerEvent->player_uuid)
                ->incrementEach([
                    'total_used' => $minecraftPlayerEvent->items_used,
                    'total_mined' => $minecraftPlayerEvent->items_mined,
                    'total_picked_up' => $minecraftPlayerEvent->items_picked_up,
                    'total_dropped' => $minecraftPlayerEvent->items_dropped,
                    'total_broken' => $minecraftPlayerEvent->items_broken,
                    'total_crafted' => $minecraftPlayerEvent->items_crafted,
                    'total_mob_kills' => $minecraftPlayerEvent->mob_kills,
                    'total_player_kills' => $minecraftPlayerEvent->player_kills,
                    'total_deaths' => $minecraftPlayerEvent->deaths,
                    'total_fish_caught' => $minecraftPlayerEvent->fish_caught,
                    'total_items_enchanted' => $minecraftPlayerEvent->items_enchanted,
                    'total_sleep_in_bed' => $minecraftPlayerEvent->times_slept_in_bed,
                    'total_jumps' => $minecraftPlayerEvent->times_jumped,
                    'total_items_placed' => $minecraftPlayerEvent->items_placed,
                    'total_items_consumed' => $minecraftPlayerEvent->items_consumed,
                    'raids_won' => $minecraftPlayerEvent->raids_won,
                    'play_time' => $minecraftPlayerEvent->play_time,
                    'afk_time' => $minecraftPlayerEvent->afk_time,
                    'distance_traveled' => $minecraftPlayerEvent->distance_traveled,
                    'distance_traveled_on_land' => $minecraftPlayerEvent->distance_traveled_on_land,
                    'distance_traveled_on_water' => $minecraftPlayerEvent->distance_traveled_on_water,
                    'distance_traveled_on_air' => $minecraftPlayerEvent->distance_traveled_on_air,
                    'pvp_damage_given' => $minecraftPlayerEvent->pvp_damage_given,
                    'pvp_damage_taken' => $minecraftPlayerEvent->pvp_damage_taken,
                    'total_leave_game' => $sessionEndedAt ? 1 : 0,
                ], [
                    'ip_address' => $minecraftPlayerEvent->ip_address,
                    'username' => $minecraftPlayerEvent->player_username,
                    'last_seen_at' => $sessionEndedAt ?? now(),
                ]);
        }, 5);
    }
}

<?php

namespace App\Jobs;

use App\Models\Country;
use App\Models\JsonMinecraftPlayerStat;
use App\Models\Player;
use App\Settings\PlayerSettings;
use App\Utils\PlayerRating\PlayerScoreCalculator;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculatePlayersJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('longtask');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PlayerSettings $playerSettings)
    {
        $playerSettings->refresh();

        // Check and set a flag if this is likely the first run
        $isFirstTime = JsonMinecraftPlayerStat::where('player_id', '!=', null)->exists() ? false : true;
        $shouldForceRunForAllPlayers = Cache::pull('CalculatePlayersJob::forceRunForAllPlayers', false);

        // Get All players from minecraft_player_stats & loop thru them. Get using LazyCollection for memory saving
        $rawMcPlayersList = JsonMinecraftPlayerStat::select([
            'uuid',
            DB::raw('MAX(username) as username'),
            DB::raw('MAX(ip_address) as ip_address'),
            DB::raw('MAX(last_modified) as last_seen_timestamp'),
            DB::raw('MIN(last_modified) as first_seen_timestamp'),
            DB::raw('MAX(updated_at) as last_mps_updated_at'),
            DB::raw('SUM(total_used) as total_used'),
            DB::raw('SUM(total_mined) as total_mined'),
            DB::raw('SUM(total_picked_up) as total_picked_up'),
            DB::raw('SUM(total_dropped) as total_dropped'),
            DB::raw('SUM(total_broken) as total_broken'),
            DB::raw('SUM(total_crafted) as total_crafted'),
            DB::raw('SUM(total_mob_kills) as total_mob_kills'),
            DB::raw('SUM(total_player_kills) as total_player_kills'),
            DB::raw('SUM(total_deaths) as total_deaths'),
            DB::raw('SUM(total_damage_dealt) as total_damage_dealt'),
            DB::raw('SUM(total_damage_dealt_absorbed) as total_damage_dealt_absorbed'),
            DB::raw('SUM(total_damage_dealt_resisted) as total_damage_dealt_resisted'),
            DB::raw('SUM(total_damage_absorbed) as total_damage_absorbed'),
            DB::raw('SUM(total_damage_blocked_by_shield) as total_damage_blocked_by_shield'),
            DB::raw('SUM(total_damage_resisted) as total_damage_resisted'),
            DB::raw('SUM(total_damage_taken) as total_damage_taken'),
            DB::raw('SUM(total_walk_one_cm) as total_walk_one_cm'),
            DB::raw('SUM(total_fish_caught) as total_fish_caught'),
            DB::raw('SUM(total_enchant_item) as total_enchant_item'),
            DB::raw('SUM(total_play_one_minute) as total_play_one_minute'),
            DB::raw('SUM(total_sleep_in_bed) as total_sleep_in_bed'),
            DB::raw('SUM(total_jumps) as total_jumps'),
            DB::raw('SUM(total_leave_game) as total_leave_game'),
            DB::raw('SUM(money) as total_money')]
        )->groupBy('uuid')->cursor();

        foreach ($rawMcPlayersList as $player) {
            // Check this to players table and see if they are in sync or not. If they are then just continue
            $newLastMpsUpdatedAt = Carbon::parse($player->last_mps_updated_at);
            $currentPlayer = Player::whereUuid($player->uuid)->first();
            $oldLastMpsUpdatedAt = $currentPlayer?->last_mps_updated_at;
            if ($oldLastMpsUpdatedAt && $oldLastMpsUpdatedAt >= $newLastMpsUpdatedAt && !$shouldForceRunForAllPlayers) {
                continue;
            }

            // Make array of the variables we need to store
            $forSaving['username'] = $player->username;
            $forSaving['ip_address'] = $player->ip_address;
            $forSaving['last_mps_updated_at'] = $player->last_mps_updated_at;
            $forSaving['total_used'] = $player->total_used;
            $forSaving['total_mined'] = $player->total_mined;
            $forSaving['total_picked_up'] = $player->total_picked_up;
            $forSaving['total_dropped'] = $player->total_dropped;
            $forSaving['total_broken'] = $player->total_broken;
            $forSaving['total_crafted'] = $player->total_crafted;
            $forSaving['total_mob_kills'] = $player->total_mob_kills;
            $forSaving['total_player_kills'] = $player->total_player_kills;
            $forSaving['total_deaths'] = $player->total_deaths;
            $forSaving['total_damage_dealt'] = $player->total_damage_dealt;
            $forSaving['total_damage_dealt_absorbed'] = $player->total_damage_dealt_absorbed;
            $forSaving['total_damage_dealt_resisted'] = $player->total_damage_dealt_resisted;
            $forSaving['total_damage_absorbed'] = $player->total_damage_absorbed;
            $forSaving['total_damage_blocked_by_shield'] = $player->total_damage_blocked_by_shield;
            $forSaving['total_damage_resisted'] = $player->total_damage_resisted;
            $forSaving['total_damage_taken'] = $player->total_damage_taken;
            $forSaving['total_walk_one_cm'] = $player->total_walk_one_cm;
            $forSaving['total_fish_caught'] = $player->total_fish_caught;
            $forSaving['total_enchant_item'] = $player->total_enchant_item;
            $forSaving['total_play_one_minute'] = $player->total_play_one_minute;
            $forSaving['total_sleep_in_bed'] = $player->total_sleep_in_bed;
            $forSaving['total_jumps'] = $player->total_jumps;
            $forSaving['total_leave_game'] = $player->total_leave_game;
            $forSaving['total_money'] = $player->total_money;
            $forSaving['last_seen_at'] = $player->last_seen_timestamp ? $this->makeDateFromTimestamp($player->last_seen_timestamp) : null;
            // If first seen is null or is first run then it means its not added so check and add first seen. once that never change it.
            if ($isFirstTime || !$currentPlayer || !$currentPlayer->first_seen_at) {
                $forSaving['first_seen_at'] = $player->first_seen_timestamp ? $this->makeDateFromTimestamp($player->first_seen_timestamp) : null;
            }

            // Calculation of Score
            $forSaving['total_score'] = $this->calculatePlayerScore($forSaving, $playerSettings);

            // Try to get country from IP Address
            $playerCountryId = $this->getPlayerCountryIdFromIP($player->ip_address);
            $forSaving['country_id'] = $playerCountryId;

            // Store in the table
            $playerObject = Player::updateOrCreate(
                ['uuid' => $player->uuid],
                $forSaving
            );

            // If this is First Run then update the player_id binding at minecraft_player_stats table
            if ($isFirstTime) {
                JsonMinecraftPlayerStat::whereUuid($player->uuid)->update([
                    'player_id' => $playerObject->id
                ]);
            }

            Log::debug("Adding/Updating player " . $player->uuid);
        }
    }

    private function getPlayerCountryIdFromIP($ip)
    {
        if (!$ip) {
            $country = Country::where('iso_code', null)->first();
            return $country?->id;
        }

        $isoCode = null;
        try {
            $isoCode = geoip($ip)->iso_code;
        } catch (\Exception $e) {
            Log::error($e);
        }
        $country = Country::where('iso_code', $isoCode)->first();
        return $country?->id;
    }

    private function calculatePlayerScore($player, $playerSettings)
    {
        $score = 0;
        if ($playerSettings->is_custom_score_enabled && $playerSettings->custom_score_expression) {
            try {
                $playerScoreCalculator = new PlayerScoreCalculator();
                $score = $playerScoreCalculator->calculate($playerSettings->custom_score_expression, (object)$player);
            } catch (\Exception $e) {
                \Log::critical($e);
            }
        } else {
            $score = max(($player['total_play_one_minute']/24000/3) + ($player['total_mob_kills'] + $player['total_player_kills'] + ($player['total_damage_dealt']/10)) + ($player['total_mined']/9/64) - $player['total_deaths'], 0);
        }
        return $score ?? 0;
    }

    /**
     * Detect if timestamp is in MS or Seconds and generate date accordingly
     * @param $timestamp
     * @return Carbon
     */
    private function makeDateFromTimestamp($timestamp): Carbon
    {
        $years100FromNow = now()->addYears(100)->timestamp;
        if ($timestamp > $years100FromNow)
        {
            return Carbon::createFromTimestampMs($timestamp);
        }
        return Carbon::createFromTimestamp($timestamp);
    }
}

<?php

namespace App\Utils\PlayerRating;

use App\Models\Player;
use Illuminate\Support\Str;
use NXP\MathExecutor;

class PlayerScoreCalculator implements PlayerRSCalculatorContract
{
    protected MathExecutor $executor;

    public function __construct()
    {
        $this->executor = new MathExecutor();
    }

    public function calculate(string $expression, object $originalPlayer, array $serverIds): ?float
    {
        $originalPlayer->loadMissing('minecraftPlayers');

        //clone object because we don't want to modify the original object. If we don't and later we do $player->save() it will throw error saying unknown column error because of dynamic var we adding.
        $player = clone $originalPlayer;
        $minecraftPlayers = $player->minecraftPlayers;
        // Dynamic variables for each server.
        foreach ($serverIds as $serverId) {
            // find the minecraft player for this server
            $minecraftPlayer = $minecraftPlayers->where('server_id', $serverId)->first();
            if ($minecraftPlayer) {
                $player->{'total_used__'.'server_'.$serverId} = $minecraftPlayer->items_used;
                $player->{'total_mined__'.'server_'.$serverId} = $minecraftPlayer->items_mined;
                $player->{'total_picked_up__'.'server_'.$serverId} = $minecraftPlayer->items_picked_up;
                $player->{'total_dropped__'.'server_'.$serverId} = $minecraftPlayer->items_dropped;
                $player->{'total_broken__'.'server_'.$serverId} = $minecraftPlayer->items_broken;
                $player->{'total_crafted__'.'server_'.$serverId} = $minecraftPlayer->items_crafted;
                $player->{'total_items_placed__'.'server_'.$serverId} = $minecraftPlayer->items_placed;
                $player->{'total_items_consumed__'.'server_'.$serverId} = $minecraftPlayer->items_consumed;
                $player->{'total_items_enchanted__'.'server_'.$serverId} = $minecraftPlayer->items_enchanted;
                $player->{'total_mob_kills__'.'server_'.$serverId} = $minecraftPlayer->mob_kills;
                $player->{'total_player_kills__'.'server_'.$serverId} = $minecraftPlayer->player_kills;
                $player->{'total_deaths__'.'server_'.$serverId} = $minecraftPlayer->deaths;
                $player->{'total_fish_caught__'.'server_'.$serverId} = $minecraftPlayer->fish_caught;
                $player->{'total_sleep_in_bed__'.'server_'.$serverId} = $minecraftPlayer->times_slept_in_bed;
                $player->{'raids_won__'.'server_'.$serverId} = $minecraftPlayer->raids_won;
                $player->{'play_time__'.'server_'.$serverId} = $minecraftPlayer->play_time;
                $player->{'afk_time__'.'server_'.$serverId} = $minecraftPlayer->afk_time;
                $player->{'distance_traveled__'.'server_'.$serverId} = $minecraftPlayer->distance_traveled;
                $player->{'pvp_damage_given__'.'server_'.$serverId} = $minecraftPlayer->pvp_damage_given;
                $player->{'pvp_damage_taken__'.'server_'.$serverId} = $minecraftPlayer->pvp_damage_taken;
                $player->{'total_money__'.'server_'.$serverId} = $minecraftPlayer->vault_balance;
            } else {
                $player->{'total_used__'.'server_'.$serverId} = 0;
                $player->{'total_mined__'.'server_'.$serverId} = 0;
                $player->{'total_picked_up__'.'server_'.$serverId} = 0;
                $player->{'total_dropped__'.'server_'.$serverId} = 0;
                $player->{'total_broken__'.'server_'.$serverId} = 0;
                $player->{'total_crafted__'.'server_'.$serverId} = 0;
                $player->{'total_items_placed__'.'server_'.$serverId} = 0;
                $player->{'total_items_consumed__'.'server_'.$serverId} = 0;
                $player->{'total_items_enchanted__'.'server_'.$serverId} = 0;
                $player->{'total_mob_kills__'.'server_'.$serverId} = 0;
                $player->{'total_player_kills__'.'server_'.$serverId} = 0;
                $player->{'total_deaths__'.'server_'.$serverId} = 0;
                $player->{'total_fish_caught__'.'server_'.$serverId} = 0;
                $player->{'total_sleep_in_bed__'.'server_'.$serverId} = 0;
                $player->{'raids_won__'.'server_'.$serverId} = 0;
                $player->{'play_time__'.'server_'.$serverId} = 0;
                $player->{'afk_time__'.'server_'.$serverId} = 0;
                $player->{'distance_traveled__'.'server_'.$serverId} = 0;
                $player->{'pvp_damage_given__'.'server_'.$serverId} = 0;
                $player->{'pvp_damage_taken__'.'server_'.$serverId} = 0;
                $player->{'total_money__'.'server_'.$serverId} = 0;
            }
        }

        $variablesForScoreStatic = array_keys(config('constants.variables_for_score_static'));
        $variablesForScoreDynamic = array_keys(config('constants.variables_for_score_dynamic'));

        $variablesForScoreDynamicParsed = [];
        $serverIds = array_unique($serverIds);
        foreach ($serverIds as $serverId) {
            foreach ($variablesForScoreDynamic as $variable) {
                $variablesForScoreDynamicParsed[] = str_replace('{id}', $serverId, $variable);
            }
        }

        // Regex for PHP Variable: [$a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
        // Get list of all variables that user has passed in the expression
        $variablesList = Str::of($expression)->matchAll('/[$a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*/');

        // Loop thru each variables, validates and create math executor variable for it.
        foreach ($variablesList as $variable) {
            // Validate
            if (! in_array($variable, $variablesForScoreStatic) && ! in_array($variable, $variablesForScoreDynamicParsed)) {
                continue;
            }

            // Replace
            $variableNameWithoutDollar = str_replace('$', '', $variable);
            $variableValue = $player->{$variableNameWithoutDollar};
            // Set Variable Value
            $this->executor->setVar($variableNameWithoutDollar, $variableValue);
        }

        return $this->executor->execute($expression);
    }
}

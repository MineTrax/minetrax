<?php

namespace App\Jobs;

use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Server;
use App\Settings\PlayerSettings;
use App\Utils\PlayerRating\PlayerScoreCalculator;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculatePlayersScoreJob implements ShouldQueue
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
        Log::info('[ScoreJob] Starting calculating scores for all players');
        $playerSettings->refresh();

        // id list of all servers.
        $serversIds = Server::get()->pluck('id')->toArray();

        // Check and set a flag if this is likely the first run
        $shouldForceRunForAllPlayers = Cache::pull('CalculatePlayersJob::forceRunForAllPlayers', false);

        $playerList = Player::query()->with('minecraftPlayers')->lazy();

        foreach ($playerList as $player) {
            // Check this to players table and see if they are in sync or not. If they are then just continue
            $mcPlayerDataFromServer = MinecraftPlayer::where('player_uuid', $player->uuid)
                ->selectRaw('max(updated_at) as updated_at')
                ->selectRaw('sum(vault_balance) as vault_balance')
                ->first();
            if ($mcPlayerDataFromServer->updated_at < $player->updated_at && ! $shouldForceRunForAllPlayers) {
                continue;
            }

            // Calculation of Score
            $score = $this->calculatePlayerScore($player, $playerSettings, $serversIds);
            Player::where('uuid', $player->uuid)->update([
                'total_score' => $score,
                'total_money' => $mcPlayerDataFromServer->vault_balance ?? 0,
            ]);
        }

        Log::info('[ScoreJob] Finished calculating scores for all players');
    }

    private function calculatePlayerScore($player, $playerSettings, $serversIds)
    {
        $score = 0;
        if ($playerSettings->is_custom_score_enabled && $playerSettings->custom_score_expression) {
            try {
                $playerScoreCalculator = new PlayerScoreCalculator();
                $score = $playerScoreCalculator->calculate($playerSettings->custom_score_expression, (object) $player, $serversIds);
            } catch (\Exception $e) {
                \Log::critical($e);
            }
        } else {
            $score = max(($player['play_time'] / 1200 / 3) + ($player['total_mob_kills'] + $player['total_player_kills'] + ($player['pvp_damage_given'] / 10)) + ($player['total_mined'] / 9 / 64) - $player['total_deaths'], 0);
        }

        return $score ?? 0;
    }
}

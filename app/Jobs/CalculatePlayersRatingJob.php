<?php

namespace App\Jobs;

use App\Models\Player;
use App\Models\Rank;
use App\Models\Server;
use App\Services\MinecraftServerQueryService;
use App\Settings\PlayerSettings;
use App\Settings\PluginSettings;
use App\Utils\PlayerRating\PlayerRatingCalculator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CalculatePlayersRatingJob implements ShouldQueue
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
    public function handle(PlayerSettings $playerSettings, PluginSettings $pluginSettings)
    {
        Log::info('[RatingJob] Starting calculating rating & ranks for all players');

        $server = null;
        if ($pluginSettings->enable_sync_player_ranks_from_server) {
            $server = Server::where('id', $pluginSettings->sync_player_ranks_from_server_id)->first();
        }

        $playerSettings->refresh();
        $pluginSettings->refresh();

        // id list of all servers.
        $serversIds = Server::get()->pluck('id')->toArray();

        $playersList = Player::with('minecraftPlayers')->cursor();
        $minScore = Player::min('total_score');
        $maxScore = Player::max('total_score');

        foreach ($playersList as $player) {
            $player->rating = $this->calculatePlayerRating($player, $minScore, $maxScore, $playerSettings, $serversIds);
            if ($pluginSettings->enable_sync_player_ranks_from_server && $server) {
                $player->rank_id = $this->calculatePlayerRankIdFromServerWebQuery($server->ip_address, $server->webquery_port, $player);
            } else {
                $player->rank_id = $this->calculatePlayerRankIdFromScore($player);
            }
            $player->save();
        }

        /**
         * Getting all Players and updating its position one by one.
         */
        $pTs = Player::orderByDesc('rating')->orderByDesc('total_score')->cursor();
        $position = 0;
        foreach ($pTs as $pT) {
            $pT->position = ++$position;
            $pT->save();
        }

        Log::info('[RatingJob] Finished calculating rating & ranks for all players');
    }

    private function calculatePlayerRating($player, $minScore, $maxScore, PlayerSettings $playerSettings, array $serverIds): ?int
    {
        $rating = null;
        $minScore = $minScore ?? 0;
        $maxScore = $maxScore ?? 0;
        // If user is not active for playerSetting->last_seen_day_for_active days then no rating.
        if ($playerSettings->last_seen_day_for_active !== -1 && $player->last_seen_at <= now()->subDays($playerSettings->last_seen_day_for_active)) {
            return null;
        }

        if ($playerSettings->is_custom_rating_enabled && $playerSettings->custom_rating_expression) {
            try {
                $playerRatingCalculator = new PlayerRatingCalculator();
                $rating = $playerRatingCalculator->calculate($playerSettings->custom_rating_expression, $player, $serverIds);
            } catch (\Exception $e) {
                Log::critical($e);
                $rating = null;
            }
        } else {
            // TODO: Temp to testing, change this with some good professional rating calculation
            $divideBy = ($maxScore - $minScore) == 0 ? 1 : $maxScore - $minScore;
            $rating = round(($player->total_score - $minScore) / $divideBy * 10, 2);
        }

        return $rating;
    }

    private function calculatePlayerRankIdFromScore($player): ?int
    {
        /**
         * Calculation of rank_id using total_score_needed and Rank table
         *
         * Make sure that there are ranks in ranks table if not,
         * Run php artisan db:seed
         *
         * TODO: Cache rank table to $this->rankList so that we dont have to query everytime.
         */
        $rankId = Rank::where('total_score_needed', '<=', $player->total_score ?? 0)
            ->where('total_play_time_needed', '<=', $player->play_time ?? 0)
            ->where('rating_needed', '<=', $player->rating ?? 0)
            ->orderByDesc('weight')->first()?->id;

        return $rankId;
    }

    private function calculatePlayerRankIdFromServerWebQuery($serverHost, $serverPort, $player): ?int
    {
        $rankId = null;

        try {
            $minecraftQueryService = app(MinecraftServerQueryService::class);
            $playerGroups = $minecraftQueryService->getPlayerGroupWithPluginWebQueryProtocol($serverHost, $serverPort, $player->uuid);

            if ($playerGroups && $playerGroups['groups']) {
                $rankId = Rank::whereIn('shortname', $playerGroups['groups'])
                    ->orderByDesc('weight')->first()?->id;
            }
        } catch (\Exception $e) {
            Log::critical($e);
        }

        return $rankId;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\Player;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;

class ApiRankController extends ApiController
{
    /**
     * List all users which have a player of given rank linked to them, along with their Discord ID.
     *
     * Used by external services (eg: Discord bot) to sync rank of players with Discord roles.
     *
     * @return JsonResponse
     */
    public function getRankMembers(string $rank)
    {
        $rank = Rank::where('shortname', $rank)->first();
        if (! $rank) {
            return $this->error('Rank not found.', 'rank_not_found', 404);
        }

        $members = User::whereNotNull('discord_user_id')
            ->whereHas('players', function (Builder $query) use ($rank) {
                $query->where('rank_id', $rank->id);
            })
            ->with(['players' => function (BelongsToMany $query) use ($rank) {
                $query->where('rank_id', $rank->id)
                    ->orderBy('players.id')
                    ->select(['players.id', 'players.username']);
            }])
            ->get(['id', 'username', 'discord_user_id'])
            ->flatMap(function (User $user) {
                return $user->players->map(fn (Player $player) => [
                    'player_id' => $player->id,
                    'player_username' => $player->username,
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'discord_id' => $user->discord_user_id,
                ]);
            });

        return $this->success($members, 'Ok');
    }
}

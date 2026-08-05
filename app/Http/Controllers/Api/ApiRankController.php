<?php

namespace App\Http\Controllers\Api;

use App\Models\Rank;
use App\Models\User;
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
            ->whereHas('players', function ($query) use ($rank) {
                $query->where('rank_id', $rank->id);
            })
            ->get(['id', 'username', 'discord_user_id'])
            ->map(function (User $user) {
                return [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'discord_id' => $user->discord_user_id,
                ];
            });

        return $this->success($members, 'Ok');
    }
}

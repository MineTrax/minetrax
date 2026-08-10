<?php

namespace App\Http\Controllers\Api;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ApiUserController extends ApiController
{
    /**
     * Find a MineTrax user and their linked Minecraft usernames by Discord ID.
     */
    public function showByDiscordId(string $discordId): JsonResponse
    {
        $user = User::query()
            ->where('discord_user_id', $discordId)
            ->with('players:id,username')
            ->first();

        if (! $user) {
            return $this->error('User not found.', 'user_not_found', 404);
        }

        return $this->success([
            [
                'user_id' => $user->id,
                'linked_users' => $user->players->map(fn (Player $player) => [
                    'player_id' => $player->id,
                    'username' => $player->username,
                ])->values(),
            ],
        ], 'Ok');
    }
}

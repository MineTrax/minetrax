<?php

namespace App\Policies;

use App\Models\Player;
use App\Models\User;
use App\Settings\PlayerSettings;

class PlayerPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewIntel(?User $user, Player $player)
    {
        $showPlayerIntelTo = app(PlayerSettings::class)?->show_player_intel_to;

        if ($showPlayerIntelTo === null) {
            return false;
        }

        if ($showPlayerIntelTo === 'none') {
            return false;
        }

        if ($showPlayerIntelTo === 'self') {
            $isPlayerOwner = $user?->players()->where('players.id', $player->id)->exists();

            return $user && ($user->isStaffMember() || $isPlayerOwner);
        }

        if ($showPlayerIntelTo === 'staff') {
            return $user && $user->isStaffMember();
        }

        if ($showPlayerIntelTo === 'login') {
            return (bool) $user;
        }

        if ($showPlayerIntelTo === 'all') {
            return true;
        }

        return false;
    }

    public function changeSkin(User $user, Player $player)
    {
        if ($user->can('change any_player_skin')) {
            return true;
        }

        return $user->players()->where('players.id', $player->id)->exists();
    }

    public function destroy(User $user, Player $player)
    {
        return $user->can('delete players');
    }
}

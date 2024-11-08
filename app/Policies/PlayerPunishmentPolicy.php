<?php

namespace App\Policies;

use App\Models\PlayerPunishment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlayerPunishmentPolicy
{
    public function before(?User $user): ?bool
    {
        $banwardenEnabled = config('minetrax.banwarden.enabled');
        if (!$banwardenEnabled) {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        $banwardenPublic = config('minetrax.banwarden.show_public');
        if ($banwardenPublic) {
            return true;
        }

        if ($user->can('read banwarden_punishments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, PlayerPunishment $playerPunishment): bool
    {
        $banwardenPublic = config('minetrax.banwarden.show_public');
        if ($banwardenPublic) {
            return true;
        }

        if ($user->can('read banwarden_punishments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view alts of the model.
     */
    public function viewAlts(User $user, PlayerPunishment $playerPunishment): bool
    {
        $banwardenPublic = config('minetrax.banwarden.show_public');
        if ($user->isStaffMember() && ($user->can('read banwarden_punishments') || $banwardenPublic)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view alts of the model.
     */
    public function viewCritical(User $user): bool
    {
        if ($user->can('read banwarden_punishments_critical')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('create banwarden_punishments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlayerPunishment $playerPunishment): bool
    {
        if ($user->can('update banwarden_punishments')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlayerPunishment $playerPunishment): bool
    {
        if ($user->can('delete banwarden_punishments')) {
            return true;
        }

        return false;
    }


    public function viewEvidence(User $user): bool
    {
        if ($user->can('read banwarden_punishments_evidence')) {
            return true;
        }

        return false;
    }

    public function createEvidence(User $user): bool
    {
        if ($user->can('create banwarden_punishments_evidence')) {
            return true;
        }

        return false;
    }

    public function deleteEvidence(User $user): bool
    {
        if ($user->can('delete banwarden_punishments_evidence')) {
            return true;
        }

        return false;
    }
}

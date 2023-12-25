<?php

namespace App\Policies;

use App\Models\Download;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DownloadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('read downloads')) {
            return true;
        }
    }

    public function view(User $user, Download $download)
    {
        if ($user->can('read downloads')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create downloads')) {
            return true;
        }
    }

    public function update(User $user, Download $download)
    {
        if ($user->can('update downloads')) {
            return true;
        }
    }

    public function delete(User $user, Download $download)
    {
        if ($user->can('delete downloads')) {
            return true;
        }
    }

    public function download(?User $user, Download $download)
    {
        // If admin, true
        if ($user?->can('read downloads')) {
            return true;
        }

        // If !is_active then false
        if (! $download->is_active) {
            return false;
        }

        // If is_auth_only, then user should be authenticated
        if ($download->is_auth_only && ! $user) {
            return false;
        }

        // if min_role_weight_required then check if user has role with weight >= min_role_weight_required
        if ($download->is_auth_only && $download->min_role_weight_required) {
            $userRoleWeight = $user->roles->max('weight');
            if ($userRoleWeight < $download->min_role_weight_required) {
                return false;
            }
        }

        return true;
    }
}

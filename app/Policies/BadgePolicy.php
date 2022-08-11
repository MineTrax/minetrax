<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BadgePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('read badges')) {
            return true;
        }
    }

    public function view(User $user, Badge $badge)
    {
        if ($user->can('read badges')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create badges')) {
            return true;
        }
    }

    public function update(User $user, Badge $badge)
    {
        if ($user->can('update badges')) {
            return true;
        }
    }

    public function delete(User $user, Badge $badge)
    {
        if ($user->can('delete badges')) {
            return true;
        }
    }
}

<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('read sessions')) {
            return true;
        }
    }

    public function delete(User $user, Session $session)
    {
        if ($user->can('delete sessions')) {
            return true;
        }
    }
}

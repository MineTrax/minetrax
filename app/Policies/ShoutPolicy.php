<?php

namespace App\Policies;

use App\Models\Shout;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShoutPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shout  $shout
     * @return mixed
     */
    public function view(User $user, Shout $shout)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        // @TODO: Check if user is banned or muted.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shout  $shout
     * @return mixed
     */
    public function update(User $user, Shout $shout)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shout  $shout
     * @return mixed
     */
    public function delete(User $user, Shout $shout)
    {
        if ($user->can('delete shouts')) {
            return true;
        }

        return $user->id === $shout->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shout  $shout
     * @return mixed
     */
    public function restore(User $user, Shout $shout)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Shout  $shout
     * @return mixed
     */
    public function forceDelete(User $user, Shout $shout)
    {
        //
    }
}

<?php

namespace App\Policies;

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomPagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if ($user->can('read custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, CustomPage $customPage)
    {
        if ($user->can('read custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->can('create custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return mixed
     */
    public function update(User $user, CustomPage $customPage)
    {
        if ($user->can('update custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(User $user, CustomPage $customPage)
    {
        if ($user->can('delete custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(User $user, CustomPage $customPage)
    {
        if ($user->can('delete custom_pages')) {
            return true;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, CustomPage $customPage)
    {
        if ($user->can('delete custom_pages')) {
            return true;
        }
    }
}

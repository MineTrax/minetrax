<?php

namespace App\Policies;

use App\Models\CustomForm;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomFormPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->can('read custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomForm $customForm): bool
    {
        if ($user->can('read custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can('create custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomForm $customForm): bool
    {
        if ($user->can('update custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomForm $customForm): bool
    {
        if ($user->can('delete custom_forms')) {
            return true;
        }
    }
}

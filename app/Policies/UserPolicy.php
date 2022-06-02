<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        if ($user->can('read users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function view(User $user, User $model)
    {
        if ($user->can('read users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function update(User $user, User $model)
    {
        // user can edit other user if he has update permission & his role weight is more than user he is editing
        $editorWeight = $user->roles[0]->weight;
        $targetUserWeight = $model->roles[0]->weight;
        if ($user->can('update users') && $editorWeight > $targetUserWeight) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function delete(User $user, User $model)
    {
        $editorWeight = $user->roles[0]->weight;
        $targetUserWeight = $model->roles[0]->weight;
        if ($user->can('delete users') && $editorWeight > $targetUserWeight) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }

    public function ban(User $user, User $model)
    {
        $editorWeight = $user->roles[0]->weight;
        $targetUserWeight = $model->roles[0]->weight;
        if ($user->can('ban users') && $editorWeight > $targetUserWeight) {
            // If self return false
            if ($user->id === $model->id) return false;
            return true;
        }
    }

    public function mute(User $user, User $model)
    {
        $editorWeight = $user->roles[0]->weight;
        $targetUserWeight = $model->roles[0]->weight;
        if ($user->can('mute users') && $editorWeight > $targetUserWeight) {
            // If self return false
            if ($user->id === $model->id) return false;
            return true;
        }
    }

    public function impersonate(User $user, User $model)
    {
        $editorWeight = $user->roles[0]->weight;
        $targetUserWeight = $model->roles[0]->weight;
        if ($user->can('impersonate users') && $editorWeight > $targetUserWeight) {
            // If self return false
            if ($user->id === $model->id) return false;
            return true;
        }
    }
}

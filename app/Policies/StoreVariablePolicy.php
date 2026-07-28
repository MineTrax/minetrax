<?php

namespace App\Policies;

use App\Models\StoreVariable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreVariablePolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?bool
    {
        if (! config('store.enabled')) {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return $user?->can('read store_variables') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StoreVariable $storeVariable): bool
    {
        return $user?->can('read store_variables') ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_variables') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, StoreVariable $storeVariable): bool
    {
        return $user?->can('update store_variables') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StoreVariable $storeVariable): bool
    {
        return $user?->can('delete store_variables') ?? false;
    }
}

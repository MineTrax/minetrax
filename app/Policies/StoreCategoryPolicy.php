<?php

namespace App\Policies;

use App\Models\StoreCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreCategoryPolicy
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
        return $user?->can('read store_categories') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StoreCategory $storeCategory): bool
    {
        return $user?->can('read store_categories') ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_categories') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, StoreCategory $storeCategory): bool
    {
        return $user?->can('update store_categories') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StoreCategory $storeCategory): bool
    {
        return $user?->can('delete store_categories') ?? false;
    }
}

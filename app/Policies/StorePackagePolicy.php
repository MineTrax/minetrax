<?php

namespace App\Policies;

use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePackagePolicy
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
        return $user?->can('read store_packages') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StorePackage $storePackage): bool
    {
        return $user?->can('read store_packages') ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_packages') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, StorePackage $storePackage): bool
    {
        return $user?->can('update store_packages') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StorePackage $storePackage): bool
    {
        return $user?->can('delete store_packages') ?? false;
    }
}

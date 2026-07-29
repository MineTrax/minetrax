<?php

namespace App\Policies;

use App\Models\StorePackageGrant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * A grant is what an order delivered, so it is governed by the order permissions rather than a
 * permission of its own — there is no coherent state where someone may take a perk back but not
 * see the order that sold it.
 */
class StorePackageGrantPolicy
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
        return $user?->can('read store_orders') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StorePackageGrant $grant): bool
    {
        return $user?->can('read store_orders') ?? false;
    }

    /**
     * Determine whether the user can revoke or extend the model.
     */
    public function update(?User $user, StorePackageGrant $grant): bool
    {
        return $user?->can('update store_orders') ?? false;
    }
}

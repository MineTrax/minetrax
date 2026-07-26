<?php

namespace App\Policies;

use App\Models\StoreBan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreBanPolicy
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
        return $user?->can('read store_bans') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StoreBan $storeBan): bool
    {
        return $user?->can('read store_bans') ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_bans') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, StoreBan $storeBan): bool
    {
        return $user?->can('update store_bans') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StoreBan $storeBan): bool
    {
        return $user?->can('delete store_bans') ?? false;
    }
}

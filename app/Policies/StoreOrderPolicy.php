<?php

namespace App\Policies;

use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreOrderPolicy
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
    public function view(?User $user, StoreOrder $order): bool
    {
        if ($user?->can('read store_orders')) {
            return true;
        }

        if ($user && $order->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, StoreOrder $order): bool
    {
        return $user?->can('update store_orders') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StoreOrder $order): bool
    {
        return $user?->can('delete store_orders') ?? false;
    }

    /**
     * Determine whether the user can refund the model.
     */
    public function refund(?User $user, StoreOrder $order): bool
    {
        return $user?->can('refund store_orders') ?? false;
    }

    /**
     * Determine whether the user can resend the model.
     */
    public function resend(?User $user, StoreOrder $order): bool
    {
        return $user?->can('resend store_orders') ?? false;
    }
}

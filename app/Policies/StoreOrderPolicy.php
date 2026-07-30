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

    /**
     * Determine whether the user can download the model's invoice.
     *
     * A guest order has no account to authorise against, so knowledge of the order's uuid is the
     * credential — the same rule the result page uses, and the route binds on the uuid. It is a v4
     * uuid and never appears in a listing.
     */
    public function downloadInvoice(?User $user, StoreOrder $order): bool
    {
        if (! $order->user_id) {
            return true;
        }

        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can see store revenue.
     *
     * Its own permission rather than `read store_orders`: seeing an order because support needs to
     * is not the same as seeing what the server earns.
     */
    public function viewStatistics(?User $user): bool
    {
        return $user?->can('view store_statistics') ?? false;
    }
}

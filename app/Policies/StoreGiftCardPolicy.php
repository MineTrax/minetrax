<?php

namespace App\Policies;

use App\Models\StoreGiftCard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreGiftCardPolicy
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
        return $user?->can('read store_gift_cards') ?? false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, StoreGiftCard $storeGiftCard): bool
    {
        return $user?->can('read store_gift_cards') ?? false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_gift_cards') ?? false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * Covers editing the card and adjusting its balance: both change what the holder can spend, and
     * splitting them would let staff hand out credit they cannot disable.
     */
    public function update(?User $user, StoreGiftCard $storeGiftCard): bool
    {
        return $user?->can('update store_gift_cards') ?? false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, StoreGiftCard $storeGiftCard): bool
    {
        return $user?->can('delete store_gift_cards') ?? false;
    }
}

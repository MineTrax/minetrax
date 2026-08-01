<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\ScopesToCreatorTrait;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Gift cards come from two places, and only one of them has a creator: a card bought from the store
 * is minted by the fulfilment job with `created_by` left null, while a card issued by hand records
 * the staff member who conjured it.
 *
 * That makes the `_own` tier meaningful here — staff granted only `read_own store_gift_cards` see
 * the cards they issued and nothing else, purchased cards included. Full visibility stays with
 * `read store_gift_cards`.
 *
 * `update` covers both editing a card and adjusting its balance. Splitting them would let staff
 * hand out credit they cannot then disable.
 *
 * @see ScopesToCreatorTrait for how the two tiers combine.
 */
class StoreGiftCardPolicy
{
    use HandlesAuthorization;
    use ScopesToCreatorTrait;

    public function before(?User $user): ?bool
    {
        if (! config('store.enabled')) {
            return false;
        }

        return null;
    }

    protected function permissionSubject(): string
    {
        return 'store_gift_cards';
    }

    /**
     * Determine whether the user can create models.
     *
     * Not creator-scoped: there is nothing to own yet.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_gift_cards') ?? false;
    }
}

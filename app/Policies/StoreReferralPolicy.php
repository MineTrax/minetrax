<?php

namespace App\Policies;

use App\Models\StoreReferral;
use App\Models\User;
use App\Traits\ScopesToCreatorTrait;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Referrals record the staff member who created them, so visibility splits the same two ways
 * coupons do: `read store_referrals` sees every code on the site, `read_own store_referrals` sees
 * only the codes its holder set up.
 *
 * Note the two senses of "creator" that meet here. `created_by` and ScopesToCreatorTrait mean the
 * staff member who made the record; the person being paid is the *referrer*, and lives on
 * `user_id`. They are unrelated, and the code deliberately never calls the second one a creator.
 *
 * @see ScopesToCreatorTrait for how the two permission tiers combine.
 */
class StoreReferralPolicy
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
        return 'store_referrals';
    }

    /**
     * Determine whether the user can create models.
     *
     * Not creator-scoped: there is nothing to own yet.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_referrals') ?? false;
    }

    /**
     * Record money paid out to a referrer.
     *
     * A permission of its own, held by nobody until an owner grants it. Everything else here
     * manages a promotion; this books money leaving the business, and the staff member who sets up
     * creator codes is not automatically the one who should be settling with them.
     */
    public function payout(?User $user): bool
    {
        return $user?->can('payout store_referrals') ?? false;
    }

    /**
     * See your own referral figures on the storefront.
     *
     * Nothing to do with the staff permissions above: this is a member looking at a code that names
     * them as its referrer. Owning it is the whole authorisation, so the only question left is
     * whether the store is switched on at all — which before() already answered.
     */
    public function viewDashboard(?User $user, StoreReferral $referral): bool
    {
        return $user !== null
            && $referral->user_id !== null
            && $referral->user_id === $user->id;
    }
}

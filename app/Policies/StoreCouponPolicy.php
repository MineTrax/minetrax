<?php

namespace App\Policies;

use App\Models\User;
use App\Traits\ScopesToCreatorTrait;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Coupons record the staff member who created them, so visibility can be split two ways: staff
 * granted only `read_own store_coupons` manage the codes they wrote and nothing else, while
 * `read store_coupons` is unchanged and still means every coupon on the site.
 *
 * A coupon with no creator — seeded, imported, or made before the column existed — is nobody's own
 * and shows only to the global permission.
 *
 * @see ScopesToCreatorTrait for how the two tiers combine.
 */
class StoreCouponPolicy
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
        return 'store_coupons';
    }

    /**
     * Determine whether the user can create models.
     *
     * Not creator-scoped: there is nothing to own yet.
     */
    public function create(?User $user): bool
    {
        return $user?->can('create store_coupons') ?? false;
    }
}

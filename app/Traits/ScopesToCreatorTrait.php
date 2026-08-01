<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Two-tier visibility for a resource: a global permission that sees everything, and an `_own`
 * variant that sees only what its holder created.
 *
 * `read store_coupons` is unchanged and still means every coupon, so no existing role loses
 * anything. `read_own store_coupons` is the narrow grant, for staff who should only manage what
 * they issued themselves. The same pairing applies to `update` and `delete`.
 *
 * The global permission is a superset — nobody needs to hold both, and holding `read` alone is
 * enough to see your own records too.
 *
 * A record with no creator is nobody's own, so only the global permission reveals it. That covers
 * gift cards minted by a purchase, and anything seeded or imported. It is deliberate: "mine" must
 * not quietly widen to include every row that happens to have a null column.
 *
 * Shared rather than written out per policy because this is an access rule, and a rule spelled out
 * twice is a rule that gets fixed once.
 */
trait ScopesToCreatorTrait
{
    /**
     * The permission subject for this resource, e.g. `store_coupons`.
     */
    abstract protected function permissionSubject(): string;

    /**
     * Whether this user sees everybody's records rather than only their own.
     *
     * Listings call this to decide whether to filter. It is a policy method rather than a bare
     * permission check in the controller so that the listing and the record pages cannot disagree
     * about who sees what.
     */
    public function viewAll(?User $user): bool
    {
        return $this->hasPermission($user, 'read');
    }

    public function viewAny(?User $user): bool
    {
        return $this->hasEitherPermission($user, 'read');
    }

    public function view(?User $user, Model $model): bool
    {
        return $this->hasPermissionFor($user, 'read', $model);
    }

    public function update(?User $user, Model $model): bool
    {
        return $this->hasPermissionFor($user, 'update', $model);
    }

    public function delete(?User $user, Model $model): bool
    {
        return $this->hasPermissionFor($user, 'delete', $model);
    }

    /**
     * The global permission for a verb, e.g. `update store_coupons`.
     */
    private function hasPermission(?User $user, string $verb): bool
    {
        return $user?->can($verb.' '.$this->permissionSubject()) ?? false;
    }

    /**
     * Either tier — enough to reach the listing, not enough to act on any given row.
     */
    private function hasEitherPermission(?User $user, string $verb): bool
    {
        return $this->hasPermission($user, $verb) || $this->hasPermission($user, $verb.'_own');
    }

    /**
     * The verb against one record: globally, or narrowly if this user created it.
     */
    private function hasPermissionFor(?User $user, string $verb, Model $model): bool
    {
        if ($this->hasPermission($user, $verb)) {
            return true;
        }

        return $this->hasPermission($user, $verb.'_own') && $this->wasCreatedBy($user, $model);
    }

    /**
     * A null creator is nobody's own, so it never matches.
     */
    private function wasCreatedBy(?User $user, Model $model): bool
    {
        return $user !== null
            && $model->created_by !== null
            && (int) $model->created_by === (int) $user->id;
    }
}

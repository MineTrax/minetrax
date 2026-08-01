<?php

namespace App\Policies;

use App\Models\StoreTax;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Tax rules decide what every buyer is charged, and getting one wrong is the store owner's
 * liability rather than a cosmetic bug — so this is guarded like currency, not like a coupon.
 */
class StoreTaxPolicy
{
    use HandlesAuthorization;

    public function before(?User $user): ?bool
    {
        if (! config('store.enabled')) {
            return false;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        return $user?->can('read store_taxes') ?? false;
    }

    public function view(?User $user, StoreTax $storeTax): bool
    {
        return $user?->can('read store_taxes') ?? false;
    }

    public function create(?User $user): bool
    {
        return $user?->can('create store_taxes') ?? false;
    }

    public function update(?User $user, StoreTax $storeTax): bool
    {
        return $user?->can('update store_taxes') ?? false;
    }

    public function delete(?User $user, StoreTax $storeTax): bool
    {
        return $user?->can('delete store_taxes') ?? false;
    }
}

<?php

namespace App\Listeners;

use App\Services\StoreCartService;
use Illuminate\Auth\Events\Login;

/**
 * Carries a guest's cart over when they sign in, so filling a cart and then logging in to check
 * out does not silently empty it.
 *
 * Deliberately synchronous: the merge must be visible on the very next request, which is usually
 * the redirect straight after login.
 */
class MergeGuestStoreCartOnLogin
{
    public function __construct(private StoreCartService $carts) {}

    public function handle(Login $event): void
    {
        if (! config('store.enabled')) {
            return;
        }

        $token = request()?->cookie(StoreCartService::COOKIE);

        if (! $token) {
            return;
        }

        $this->carts->mergeGuestCartInto($event->user, $token);
    }
}

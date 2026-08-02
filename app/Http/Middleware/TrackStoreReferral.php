<?php

namespace App\Http\Middleware;

use App\Services\StoreReferralService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns `?ref=SOMECODE` into a cookie, so a buyer who arrives through a creator's link still
 * credits them three days and four page loads later.
 *
 * Sits in the whole web group rather than on the store routes, because the tracking link points at
 * the site root and the store can *be* the root when homepage_route says so. The cost of that is
 * one array read per request, which is what the first line buys.
 */
class TrackStoreReferral
{
    public function __construct(private StoreReferralService $referrals) {}

    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->query('ref');

        if (! is_string($code) || $code === '' || ! config('store.enabled')) {
            return $next($request);
        }

        $referral = $this->referrals->findByCode($code);

        // Tracking can be switched off per code without disabling it, for a code meant to be typed
        // at the cart rather than followed from a link.
        if (! $referral || ! $referral->is_url_tracking_enabled) {
            return $next($request);
        }

        if ($store = $this->referrals->trackVisit($referral, $request->cookie(StoreReferralService::COOKIE))) {
            $this->referrals->rememberOnRequest($request, $store);
        }

        return $next($request);
    }
}

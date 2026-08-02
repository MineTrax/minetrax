<?php

namespace App\Services;

use App\Enums\StoreReferralAttributionMode;
use App\Models\StoreCart;
use App\Models\StoreReferral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Decides who gets credit for a visit, and for an order.
 *
 * One class answers it for the middleware, the cart and checkout, because "who is being credited"
 * has to mean the same thing in all three or a buyer sees one name and a different one gets paid.
 */
class StoreReferralService
{
    public const COOKIE = 'store_ref';

    /** A code with no attribution window follows the visitor about as long as a browser will keep it. */
    private const LIFETIME_MINUTES = 60 * 24 * 365 * 5;

    public const SOURCE_URL = 'url';

    public const SOURCE_MANUAL = 'manual';

    /**
     * An enabled, live referral for a typed or linked code.
     *
     * Case-insensitive because a buyer types this by hand off a video description, and a code that
     * only works in capitals is a support ticket waiting to happen.
     */
    public function findByCode(?string $code): ?StoreReferral
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        return StoreReferral::where('code', strtoupper($code))->where('is_enabled', true)->first();
    }

    /**
     * Who should be credited for this request, and how they were picked up.
     *
     * A code the buyer typed beats a cookie: they chose it, possibly over whatever link they
     * arrived through, and the choice they made deliberately should win.
     *
     * @return array{referral: ?StoreReferral, source: ?string}
     */
    public function resolveFor(Request $request, ?StoreCart $cart = null): array
    {
        $buyer = $request->user();

        if ($cart?->store_referral_id) {
            $typed = StoreReferral::where('id', $cart->store_referral_id)->where('is_enabled', true)->first();

            if ($this->isCreditable($typed, $buyer)) {
                return ['referral' => $typed, 'source' => self::SOURCE_MANUAL];
            }
        }

        $tracked = $this->findByCode($request->cookie(self::COOKIE));

        if ($this->isCreditable($tracked, $buyer)) {
            return ['referral' => $tracked, 'source' => self::SOURCE_URL];
        }

        return ['referral' => null, 'source' => null];
    }

    /**
     * Whether this referral may earn from this buyer.
     *
     * A code linked to a member earns nothing on that member's own orders. Otherwise anyone with a
     * code could stand a discount up against a commission and buy from themselves at a permanent
     * markdown, which is not what a referral programme is.
     */
    public function isCreditable(?StoreReferral $referral, ?User $buyer): bool
    {
        if (! $referral || ! $referral->is_enabled) {
            return false;
        }

        return ! ($buyer && $referral->user_id && $referral->user_id === $buyer->id);
    }

    /**
     * Record a `?ref=` arrival: bump the counter and decide what the visitor now carries.
     *
     * Returns the code that should be in the cookie afterwards, or null to leave it alone.
     */
    public function trackVisit(StoreReferral $arriving, ?string $existingCode): ?string
    {
        $arriving->increment('visit_count', 1, ['last_visited_at' => now()]);

        $existing = $existingCode !== null && strtoupper($existingCode) !== $arriving->code
            ? $this->findByCode($existingCode)
            : null;

        // Nothing to displace, so every mode does the same thing.
        if ($existingCode === null || ! $existing) {
            return $arriving->code;
        }

        return match ($arriving->attribution_mode) {
            StoreReferralAttributionMode::LAST_TOUCH => $arriving->code,
            // The stored code keeps the credit; queueing it again is what resets the clock, and it
            // is measured against *that* code's window, not the arriving one's.
            StoreReferralAttributionMode::EXTEND_WINDOW => $existing->code,
            StoreReferralAttributionMode::FIRST_TOUCH => null,
        };
    }

    /**
     * Put a code in the visitor's hands for as long as its window says.
     *
     * Cookie::queue rather than $response->withCookie(): an Inertia response has no withCookie(),
     * which is the same reason StoreCartController::rememberCart() queues its token. The request's
     * own bag is set too, so anything later in this request sees the code rather than waiting for
     * the next one.
     */
    public function rememberOnRequest(Request $request, string $code): void
    {
        // The window belongs to the code being stored, which under extend_window is the one that
        // was already there rather than the one that just arrived.
        $window = $this->findByCode($code)?->attribution_window_days;

        Cookie::queue(self::COOKIE, $code, $window ? $window * 60 * 24 : self::LIFETIME_MINUTES);

        $request->cookies->set(self::COOKIE, $code);
    }

    /**
     * Stop crediting anyone, for a buyer who asked to.
     */
    public function forget(Request $request): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));

        $request->cookies->remove(self::COOKIE);
    }
}

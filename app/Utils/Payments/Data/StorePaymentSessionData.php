<?php

namespace App\Utils\Payments\Data;

/**
 * The result of opening a hosted checkout.
 *
 * `redirectUrl` is null for gateways that need no redirect at all (the manual one), in which
 * case the buyer goes straight to the order result page.
 */
class StorePaymentSessionData
{
    public function __construct(
        public readonly ?string $redirectUrl = null,
        public readonly ?string $sessionId = null,
        public readonly array $raw = [],
    ) {}
}

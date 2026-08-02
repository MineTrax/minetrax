<?php

namespace App\Contracts;

use App\Enums\StorePaymentGateway;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\Data\StorePaymentSessionData;
use Illuminate\Http\Request;

/**
 * A payment gateway driver.
 *
 * Adding a gateway is one new class implementing this contract plus one line in
 * config/store.php -> gateways. Nothing else: credentials render themselves from
 * settingsSchema(), and the webhook route is a single dynamic {gateway} endpoint.
 */
interface StorePaymentGatewayContract
{
    /** Identity. The enum value is stored on orders and appears in the webhook URL. */
    public function gateway(): StorePaymentGateway;

    /** Shown on the checkout gateway picker. */
    public function label(): string;

    public function description(): ?string;

    /**
     * Whether the money moves outside the site entirely.
     *
     * An offline driver has no hosted page to send the buyer to, so "resume payment" would put them
     * back where they already are. The screens use this to offer instructions instead of a button
     * that reloads the page and charges nothing.
     */
    public function isOffline(): bool;

    /**
     * Rich text shown to the buyer while an order placed through this driver is still unpaid.
     *
     * Only an offline method has anything to say here — a hosted gateway takes the money on its own
     * page — so this is null for everything but the manual driver. Sanitised at render, never
     * trusted as markup on the way in.
     */
    public function paymentInstructions(): ?string;

    /**
     * Self-describing credential fields, so the admin settings form needs no per-gateway markup.
     *
     * `type` is one of text, textarea, select or richtext.
     *
     * @return array<int, array{key: string, label: string, type: string, required?: bool, secret?: bool, help?: string}>
     */
    public function settingsSchema(): array;

    /** Whether every required credential is present and the admin has switched this driver on. */
    public function isEnabled(): bool;

    /**
     * Currencies this driver can charge, or null for "any". Checkout hides a gateway that cannot
     * charge the selected currency rather than failing at the gateway's own page.
     *
     * @return array<int, string>|null
     */
    public function supportedCurrencies(): ?array;

    /** Begin a hosted checkout and return where to send the buyer. */
    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData;

    /**
     * Reopen the checkout a buyer walked away from, or null if it can no longer be used.
     *
     * Reusing rather than replacing is the whole point: a buyer clicking "continue payment" three
     * times must not leave three live sessions against one order. Two of those could each be
     * captured, and markPaid() would credit only the first — money taken with nothing to show for
     * it. Returning null tells the caller the session is dead and a fresh one is safe to open.
     */
    public function resumePaymentSession(StorePayment $payment): ?StorePaymentSessionData;

    /**
     * Best-effort: close a session the buyer has abandoned, before opening one elsewhere.
     *
     * Called when somebody switches gateway mid-payment. Must never throw — failing to tidy up an
     * unpaid session is not a reason to stop the buyer paying by another means. A gateway with no
     * way to void an unapproved order simply lets it expire.
     */
    public function abandonPaymentSession(StorePayment $payment): void;

    /** Cryptographically verify an inbound webhook against the raw request body. */
    public function verifyWebhook(Request $request): bool;

    /** Normalise a vendor webhook into the shared event shape. */
    public function parseWebhook(Request $request): StoreGatewayEventData;

    /**
     * Server-side confirmation when the buyer lands back on the return URL, for gateways that
     * capture on return rather than purely by webhook. Null when there is nothing to confirm.
     */
    public function confirmOnReturn(StorePayment $payment): ?StoreGatewayEventData;

    /** Issue a full or partial refund; returns the gateway's refund id. */
    public function refund(StorePayment $payment, int $amountMinor, ?string $reason = null): string;
}

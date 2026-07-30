<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreOrderStatus: string implements HasKeyValueSerialization
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case CHARGEBACK = 'chargeback';

    /**
     * The authoritative transition map. StoreOrderService guards every transition with this, so
     * an out-of-order webhook (a refund arriving before the capture, say) is rejected rather
     * than corrupting the order.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::PAID, self::CANCELLED],
            // A paid order accepts every way money can go back, not just a full refund. Delivery
            // normally moves it to COMPLETED within seconds, but a stalled queue worker can leave it
            // here for hours — and a dispute or a partial refund landing in that window must be
            // recorded rather than rejected, or the gateway refunds money the site never books.
            self::PAID => [self::COMPLETED, self::CANCELLED, self::REFUNDED, self::PARTIALLY_REFUNDED, self::CHARGEBACK],
            self::COMPLETED => [self::REFUNDED, self::PARTIALLY_REFUNDED, self::CHARGEBACK],
            // Stays on itself: several partial refunds against one order are legitimate, and only
            // the one that finally covers the full amount moves it to REFUNDED.
            self::PARTIALLY_REFUNDED => [self::PARTIALLY_REFUNDED, self::REFUNDED, self::CHARGEBACK],
            self::CANCELLED, self::REFUNDED, self::CHARGEBACK => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Whether the buyer has paid. Purchase limits and stock count only these, so cancelling an
     * abandoned order automatically restocks it.
     */
    public function isPaidState(): bool
    {
        return in_array($this, [
            self::PAID,
            self::COMPLETED,
            self::PARTIALLY_REFUNDED,
        ], true);
    }

    /**
     * Terminal states never transition again.
     */
    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    /**
     * States where the buyer has lost entitlement and grants should be revoked.
     */
    public function isRevoking(): bool
    {
        return in_array($this, [self::REFUNDED, self::CHARGEBACK], true);
    }

    /**
     * Whether money ever changed hands, and so whether there is anything to invoice.
     *
     * Broader than isPaidState(): a fully refunded or charged-back order is no longer an entitlement
     * but it still needs its paper trail. A pending order is a basket and a cancelled one is nothing
     * at all, so neither has an invoice to issue.
     */
    public function isInvoiceable(): bool
    {
        return in_array($this, [
            self::PAID,
            self::COMPLETED,
            self::PARTIALLY_REFUNDED,
            self::REFUNDED,
            self::CHARGEBACK,
        ], true);
    }
}

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
            self::PAID => [self::COMPLETED, self::CANCELLED, self::REFUNDED],
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
}

<?php

namespace App\Utils\Payments\Data;

/**
 * A gateway webhook, normalised.
 *
 * Each driver maps its own vendor event names onto `kind`, so the webhook controller and the
 * order state machine never learn a vendor's vocabulary.
 */
class StoreGatewayEventData
{
    public const KIND_COMPLETED = 'completed';

    public const KIND_FAILED = 'failed';

    public const KIND_REFUNDED = 'refunded';

    public const KIND_CHARGEBACK = 'chargeback';

    /** An event this driver recognises but the store does not act on. */
    public const KIND_IGNORED = 'ignored';

    public function __construct(
        public readonly string $eventId,
        public readonly string $kind,
        public readonly ?string $sessionId = null,
        public readonly ?string $transactionId = null,
        public readonly ?int $amountMinor = null,
        public readonly ?string $currency = null,
        public readonly ?int $feeMinor = null,
        public readonly ?string $orderUuid = null,
        public readonly ?string $failureReason = null,
        public readonly array $raw = [],
    ) {}

    public function isIgnored(): bool
    {
        return $this->kind === self::KIND_IGNORED;
    }
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePaymentStatus: string implements HasKeyValueSerialization
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case CHARGEBACK = 'chargeback';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePaymentRefundType: string implements HasKeyValueSerialization
{
    case REFUND = 'refund';
    case CHARGEBACK = 'chargeback';
}

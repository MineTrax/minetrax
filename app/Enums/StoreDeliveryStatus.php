<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreDeliveryStatus: string implements HasKeyValueSerialization
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
}

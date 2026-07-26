<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreDiscountType: string implements HasKeyValueSerialization
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
}

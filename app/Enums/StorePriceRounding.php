<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePriceRounding: string implements HasKeyValueSerialization
{
    case NONE = 'none';
    case NEAREST_WHOLE = 'nearest_whole';
    case NEAREST_HALF = 'nearest_half';
    case CHARM_99 = 'charm_99';
}

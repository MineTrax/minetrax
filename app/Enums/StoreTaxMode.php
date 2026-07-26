<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreTaxMode: string implements HasKeyValueSerialization
{
    case NONE = 'none';
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';
}

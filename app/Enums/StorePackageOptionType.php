<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePackageOptionType: string implements HasKeyValueSerialization
{
    case SELECT = 'select';
    case TEXT = 'text';
    case NUMBER = 'number';
}

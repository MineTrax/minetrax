<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StoreCommandTarget: string implements HasKeyValueSerialization
{
    case PACKAGE_SERVERS = 'package_servers';
    case ALL_SERVERS = 'all_servers';
}

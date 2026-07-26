<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePackageGrantStatus: string implements HasKeyValueSerialization
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';
}

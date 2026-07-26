<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum StorePackageCommandTrigger: string implements HasKeyValueSerialization
{
    case PURCHASE = 'purchase';
    case EXPIRY = 'expiry';
    case REFUND = 'refund';
    case CHARGEBACK = 'chargeback';
}

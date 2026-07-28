<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * How a package's list of required packages is read: the buyer must own every one of them, or
 * any single one is enough.
 */
enum StorePackageRequirementMode: string implements HasKeyValueSerialization
{
    case ALL = 'all';
    case ANY = 'any';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * What a sale covers.
 *
 * Held as an explicit choice rather than inferred from whether store_saleables has rows: inferring
 * meant an admin who cleared the package picker silently turned a targeted sale into a store-wide
 * one, with nothing on screen to say so.
 */
enum StoreSaleScope: string implements HasKeyValueSerialization
{
    case ALL = 'all';
    case CATEGORIES = 'categories';
    case PACKAGES = 'packages';
}

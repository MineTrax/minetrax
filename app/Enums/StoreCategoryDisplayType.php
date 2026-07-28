<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * How a category lays its packages out on the storefront.
 *
 * The choice is per category rather than site-wide because the right layout depends on what is in
 * it: three ranks compare well in a table, twenty crate keys do not.
 */
enum StoreCategoryDisplayType: string implements HasKeyValueSerialization
{
    /** Cards in a grid. The default, and the right answer for a lot of packages. */
    case GRID = 'grid';

    /** A table, one column per package and one row per comparison field. */
    case COMPARISON = 'comparison';

    /** A vertical list, one row per package. Good for a middling number. */
    case LISTING = 'listing';

    /** A list with the quantity front and centre, for bulk items sold by the unit. */
    case STACKED = 'stacked';

    /**
     * Whether this layout reads the category's comparison fields.
     */
    public function usesComparisonFields(): bool
    {
        return $this === self::COMPARISON;
    }
}

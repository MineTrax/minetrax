<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * How a comparison table cell is rendered.
 */
enum StoreComparisonFieldType: string implements HasKeyValueSerialization
{
    /** Whatever the admin typed. Authored HTML, so it is sanitised before being injected. */
    case TEXT = 'text';

    /** A tick or a cross, from a boolean. */
    case CHECK = 'check';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

/**
 * The input a buyer is shown for a store variable.
 *
 * These are FormKit input names on purpose: the storefront renders variables through the same
 * FormKitSchema the custom forms use, so a type here needs no frontend mapping.
 */
enum StoreVariableType: string implements HasKeyValueSerialization
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case SELECT = 'select';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';

    /**
     * Whether the buyer picks from a fixed list, which is what makes `options` required.
     */
    public function hasOptions(): bool
    {
        return in_array($this, [self::SELECT, self::RADIO], true);
    }

    public function isFreeText(): bool
    {
        return in_array($this, [self::TEXT, self::TEXTAREA], true);
    }
}

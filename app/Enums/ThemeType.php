<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static LIGHT_BLUE()
 * @method static static PURPLE()
 * @method static static GREEN()
 * @method static static ORANGE()
 */
final class ThemeType extends Enum
{
    const LIGHT_BLUE = 'light-blue';
    const PURPLE = 'purple';
    const GREEN = 'green';
    const ORANGE = 'orange';

    /**
     * Get the description for display
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::LIGHT_BLUE:
                return 'Sky Blue Theme';
            case self::PURPLE:
                return 'Purple Theme';
            case self::GREEN:
                return 'Nature Green Theme';
            case self::ORANGE:
                return 'Warm Orange Theme';
            default:
                return parent::getDescription($value);
        }
    }

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'description' => self::getDescription($this->value),
        ];
    }
}

<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class FontType extends Enum
{
    const Nunito = 'Nunito';
    const Ubuntu = 'Ubuntu';
    const Inter = 'Inter';
    const Poppins = 'Poppins';

    /**
     * Get the description for display
     */
    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Nunito:
                return 'Nunito (Rounded & Friendly)';
            case self::Ubuntu:
                return 'Ubuntu (Clean & Modern)';
            case self::Inter:
                return 'Inter (Professional)';
            case self::Poppins:
                return 'Poppins (Geometric)';
            default:
                return $value;
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

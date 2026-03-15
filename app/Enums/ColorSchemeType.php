<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SKY()
 * @method static static SAFFRON()
 * @method static static CAFFIENE()
 */
final class ColorSchemeType extends Enum
{
    const SKY = 'sky';
    const SAFFRON = 'saffron';
    const CAFFIENE = 'caffeine';

    /**
     * Google Fonts URLs needed for each color scheme.
     * System fonts (Inter, Georgia, SF Mono, etc.) don't need entries.
     */
    public static function fontUrls(string $scheme): array
    {
        $map = [
            self::SKY => [
                'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap',
            ],
            self::SAFFRON => [
                'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&display=swap',
            ],
            self::CAFFIENE => [
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            ],
        ];

        return $map[$scheme] ?? $map[self::SKY];
    }

    public function toArray(): mixed
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}

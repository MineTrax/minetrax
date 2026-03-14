<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SKY()
 * @method static static BLUE()
 * @method static static RED()
 * @method static static ORANGE()
 * @method static static LIME()
 * @method static static GREEN()
 * @method static static TEAL()
 * @method static static INDIGO()
 * @method static static FUCHSIA()
 */
final class ColorSchemeType extends Enum
{
    const SKY = 'sky';

    const BLUE = 'blue';

    const RED = 'red';

    const ORANGE = 'orange';

    const LIME = 'lime';

    const GREEN = 'green';

    const TEAL = 'teal';

    const INDIGO = 'indigo';

    const FUCHSIA = 'fuchsia';

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
            self::ORANGE => [
                'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&display=swap',
            ],
            self::RED => [
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            ],
            // Add more schemes here as needed.
            // Schemes not listed will default to SKY fonts.
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

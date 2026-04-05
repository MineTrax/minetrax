<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SKY()
 * @method static static SAFFRON()
 * @method static static CAFFIENE()
 * @method static static LIME()
 * @method static static MOSS()
 * @method static static CLAUDE()
 */
final class ColorSchemeType extends Enum
{
    const SKY = 'sky';

    const SAFFRON = 'saffron';

    const CAFFIENE = 'caffeine';

    const NEO_BRUTALISM = 'neobrutalism';

    const NOTEBOOK = 'notebook';

    const LIME = 'lime';

    const MOSS = 'moss';

    const CLAUDE = 'claude';

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
            self::NEO_BRUTALISM => [
                'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap',
            ],
            self::NOTEBOOK => [
                'https://fonts.googleapis.com/css2?family=Architects+Daughter:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Georgia:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&display=swap',
            ],
            self::LIME => [
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap',
            ],
            self::MOSS => [
                'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap',
            ],
            self::CLAUDE => [
                'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&display=swap',
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

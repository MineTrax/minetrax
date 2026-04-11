<?php

namespace App\Enums;

use App\Enums\Concerns\HasKeyValueSerialization;

enum ColorSchemeType: string implements HasKeyValueSerialization
{
    case SKY = 'sky';
    case SAFFRON = 'saffron';
    case CAFFIENE = 'caffeine';
    case NEO_BRUTALISM = 'neobrutalism';
    case NOTEBOOK = 'notebook';
    case LIME = 'lime';
    case MOSS = 'moss';
    case CLAUDE = 'claude';

    /**
     * Google Fonts URLs needed for each color scheme.
     * System fonts (Inter, Georgia, SF Mono, etc.) don't need entries.
     */
    public static function fontUrls(string $scheme): array
    {
        $map = [
            self::SKY->value => [
                'https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap',
            ],
            self::SAFFRON->value => [
                'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&display=swap',
            ],
            self::CAFFIENE->value => [
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
            ],
            self::NEO_BRUTALISM->value => [
                'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap',
            ],
            self::NOTEBOOK->value => [
                'https://fonts.googleapis.com/css2?family=Architects+Daughter:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Georgia:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&display=swap',
            ],
            self::LIME->value => [
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap',
            ],
            self::MOSS->value => [
                'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap',
            ],
            self::CLAUDE->value => [
                'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap',
                'https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&display=swap',
            ],
        ];

        return $map[$scheme] ?? $map[self::SKY->value];
    }

    public static function asSelectArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->name;
        }

        return $array;
    }
}

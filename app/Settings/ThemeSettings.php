<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    public string $color_mode;
    public string $theme_name;
    public string $primary_font;
    public string $secondary_font;

    public static function group(): string
    {
        return 'theme';
    }
}

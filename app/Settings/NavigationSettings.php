<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NavigationSettings extends Settings
{
    public bool $enable_custom_navbar;
    public array $custom_navbar_data;
    public bool $enable_custom_footer;
    public ?array $custom_footer_data;

    public static function group(): string
    {
        return 'navigation';
    }
}

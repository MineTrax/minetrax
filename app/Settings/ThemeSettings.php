<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ThemeSettings extends Settings
{
    public string $color_mode;

    public string $theme_name;

    public string $primary_font;

    public string $secondary_font;

    public bool $enable_home_hero_section;

    public string $home_hero_bg_image_path_dark;

    public string $home_hero_bg_image_path_light;

    public string $home_hero_bg_size_css;

    public string $home_hero_bg_height_css;

    public string $home_hero_bg_position_css;

    public string $home_hero_bg_repeat_css;

    public string $home_hero_bg_attachment_css;

    public bool $show_join_box_in_home_hero;

    public ?string $home_hero_fg_image_path_dark;

    public ?string $home_hero_fg_image_path_light;

    public bool $show_fg_image_box_in_home_hero;

    public bool $show_discord_box_in_home_hero;

    public ?string $home_hero_bg_particles;

    public ?string $loading_gif;

    public static function group(): string
    {
        return 'theme';
    }
}

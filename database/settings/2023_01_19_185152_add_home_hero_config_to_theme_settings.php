<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddHomeHeroConfigToThemeSettings extends SettingsMigration
{
    public function up(): void
    {
        // Hero section (theme)
        $this->migrator->add('theme.enable_home_hero_section', true);
        $this->migrator->add('theme.home_hero_bg_image_path_dark', '/images/def_home_hero_bg_image_dark.webp');
        $this->migrator->add('theme.home_hero_bg_image_path_light', '/images/def_home_hero_bg_image_light.webp');
        $this->migrator->add('theme.home_hero_bg_size_css', 'cover');
        $this->migrator->add('theme.home_hero_bg_height_css', '45vh');
        $this->migrator->add('theme.home_hero_bg_position_css', 'center center');
        $this->migrator->add('theme.home_hero_bg_repeat_css', 'no-repeat');
        $this->migrator->add('theme.home_hero_bg_attachment_css', 'scroll');
        $this->migrator->add('theme.show_join_box_in_home_hero', true);

        // Main header should be sticky.(general)
        $this->migrator->add('general.enable_sticky_header_menu', true);
    }
}

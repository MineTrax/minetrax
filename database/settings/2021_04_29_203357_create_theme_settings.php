<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateThemeSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.color_mode', 'dark'); // light, dark
        $this->migrator->add('theme.theme_name', 'light-blue');
        $this->migrator->add('theme.primary_font', 'Nunito');
        $this->migrator->add('theme.secondary_font', 'Nunito');
    }
}

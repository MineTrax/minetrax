<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateNavigationSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('navigation.enable_custom_navbar', false);
        $this->migrator->add('navigation.custom_navbar_data', []);
    }
}

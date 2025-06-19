<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.color_scheme', 'sky');
        $this->migrator->deleteIfExists('theme.theme_name');
        $this->migrator->deleteIfExists('theme.secondary_font');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('theme.color_scheme');
        $this->migrator->add('theme.theme_name', 'light');
        $this->migrator->add('theme.secondary_font', 'light');
    }
};

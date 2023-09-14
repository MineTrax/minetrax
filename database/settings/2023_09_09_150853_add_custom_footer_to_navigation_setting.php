<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('navigation.enable_custom_footer', false);
        $this->migrator->add('navigation.custom_footer_data', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('navigation.enable_custom_footer');
        $this->migrator->deleteIfExists('navigation.custom_footer_data');
    }
};

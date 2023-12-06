<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('theme.loading_gif', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('theme.loading_gif');
    }
};

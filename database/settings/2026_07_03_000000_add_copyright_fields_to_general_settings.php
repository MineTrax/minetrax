<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.copyright_name', null);
        $this->migrator->add('general.copyright_url', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('general.copyright_name');
        $this->migrator->deleteIfExists('general.copyright_url');
    }
};

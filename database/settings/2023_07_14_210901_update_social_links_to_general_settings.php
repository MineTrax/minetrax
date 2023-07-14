<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.linkedin_url', null);
        $this->migrator->add('general.tiktok_url', null);
        $this->migrator->add('general.threads_url', null);
    }
};

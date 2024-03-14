<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.instagram_url', null);
        $this->migrator->add('general.whatsapp_url', null);
        $this->migrator->add('general.telegram_url', null);
        $this->migrator->add('general.reddit_url', null);
        $this->migrator->add('general.github_url', null);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('general.instagram_url');
        $this->migrator->deleteIfExists('general.whatsapp_url');
        $this->migrator->deleteIfExists('general.telegram_url');
        $this->migrator->deleteIfExists('general.reddit_url');
        $this->migrator->deleteIfExists('general.github_url');
    }
};

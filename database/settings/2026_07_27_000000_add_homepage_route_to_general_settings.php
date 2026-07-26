<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Which page `/` renders. Site-wide rather than a store setting, so future modules extend the
     * same allowlist instead of each adding a competing "make me the homepage" flag.
     */
    public function up(): void
    {
        $this->migrator->add('general.homepage_route', 'dashboard');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('general.homepage_route');
    }
};

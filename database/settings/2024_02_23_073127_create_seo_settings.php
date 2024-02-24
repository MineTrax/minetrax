<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.favicon_path', null);
        $this->migrator->add('seo.title_home', null);
        $this->migrator->add('seo.title_suffix', null);
        $this->migrator->add('seo.meta', []);

        $this->migrator->add('seo.robots_txt', null);

        $this->migrator->add('seo.inject_at_head', null); // any custom code to inject at head
        $this->migrator->add('seo.inject_at_body_start', null); // any custom code to inject at body (after opening body tag)
        $this->migrator->add('seo.inject_at_body_end', null); // any custom code to inject at body (before closing body tag)
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('seo.favicon_path');
        $this->migrator->deleteIfExists('seo.title_home');
        $this->migrator->deleteIfExists('seo.title_suffix');
        $this->migrator->deleteIfExists('seo.meta');

        $this->migrator->deleteIfExists('seo.robots_txt');

        $this->migrator->deleteIfExists('seo.inject_at_head');
        $this->migrator->deleteIfExists('seo.inject_at_body_start');
        $this->migrator->deleteIfExists('seo.inject_at_body_end');
    }
};

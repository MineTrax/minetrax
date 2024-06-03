<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('plugin.account_link_after_success_commands', []);
        $this->migrator->add('plugin.account_unlink_after_success_commands', []);
        $this->migrator->deleteIfExists('plugin.account_link_after_success_command');
        $this->migrator->deleteIfExists('plugin.account_link_after_success_broadcast_message');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('plugin.account_link_after_success_commands');
        $this->migrator->deleteIfExists('plugin.account_unlink_after_success_commands');
        $this->migrator->add('plugin.account_link_after_success_command', null);
        $this->migrator->add('plugin.account_link_after_success_broadcast_message', null);
    }
};

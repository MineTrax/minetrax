<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('plugin.enable_player_password_reset', false);
        $this->migrator->add('plugin.player_password_reset_commands', []);
        $this->migrator->add('plugin.player_password_reset_cooldown_in_seconds', 60);
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('plugin.enable_player_password_reset');
        $this->migrator->deleteIfExists('plugin.player_password_reset_commands');
        $this->migrator->deleteIfExists('plugin.player_password_reset_cooldown_in_seconds');
    }
};

<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        /**
         * Player Setting
         * Show Player Intel To:
         * none -> no one will be able to view player intel except the superadmin
         * staff -> only staff members can view player intel
         * self -> only the user who linked the player, can view intel for that player (staff will be able to view too)
         * login -> any authenticated user can view player intel.
         * all -> public, anyone visiting website can view anyone Player Intel
         */
        $this->migrator->add('player.show_player_intel_to', 'self');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('player.show_player_intel_to');
    }
};

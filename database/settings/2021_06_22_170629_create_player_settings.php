<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePlayerSettings extends SettingsMigration
{
    public function up(): void
    {
        /**
         * General Setting or Player Setting????
         * Show Player Intel To:
         * none -> no one will be able to view player intel except the superadmin
         * staff -> only staff members can view player intel
         * self -> only the user who linked the player, can view intel for that player (staff will be able to view too)
         * login -> any authenticated user can view player intel.
         * all -> public, anyone visiting website can view anyone Player Intel
         */
         //  $this->migrator->add('show_player_intel_to', 'all');

        $this->migrator->add('player.is_custom_rating_enabled', false);
        $this->migrator->add('player.custom_rating_expression', null);
        $this->migrator->add('player.last_seen_day_for_active', 30);
        $this->migrator->add('player.is_custom_score_enabled', false);
        $this->migrator->add('player.custom_score_expression', null);
    }
}

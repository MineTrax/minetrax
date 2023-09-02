<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePlayerSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('player.is_custom_rating_enabled', false);
        $this->migrator->add('player.custom_rating_expression', null);
        $this->migrator->add('player.last_seen_day_for_active', 30);
        $this->migrator->add('player.is_custom_score_enabled', false);
        $this->migrator->add('player.custom_score_expression', null);
    }
}

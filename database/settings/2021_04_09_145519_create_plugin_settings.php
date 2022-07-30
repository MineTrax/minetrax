<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePluginSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('plugin.enable_api', true);
        $this->migrator->addEncrypted('plugin.plugin_api_key', Str::random(32));
        $this->migrator->addEncrypted('plugin.plugin_api_secret', Str::random(32));

        $this->migrator->add('plugin.enable_account_link', true);
        $this->migrator->add('plugin.max_players_link_per_account', 1);
        $this->migrator->add('plugin.account_link_after_success_command', null);
        $this->migrator->add('plugin.account_link_after_success_broadcast_message', null);

        $this->migrator->add('plugin.enable_sync_player_ranks_from_server', false);
        $this->migrator->add('plugin.sync_player_ranks_from_server_id', null);
    }
}

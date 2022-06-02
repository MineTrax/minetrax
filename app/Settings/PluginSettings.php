<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PluginSettings extends Settings
{
    public bool $enable_api;
    public string $plugin_api_key;
    public string $plugin_api_secret;

    public bool $enable_account_link;
    public int $max_players_link_per_account;
    public ?string $account_link_after_success_command;
    public ?string $account_link_after_success_broadcast_message;

    public bool $enable_sync_player_ranks_from_server;
    public ?int $sync_player_ranks_from_server_id;

    public static function group(): string
    {
        return 'plugin';
    }

    public static function encrypted(): array
    {
        return [
            'plugin_api_key',
            'plugin_api_secret'
        ];
    }
}

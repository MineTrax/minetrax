<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public ?string $site_name;
    public ?string $site_header_logo_path_light;
    public ?string $site_header_logo_path_dark;

    public bool $enable_mcserver_onlineplayersbox;
    public bool $enable_mcserver_statuspingbox;
    public bool $enable_ingamechat;
    public bool $enable_shoutbox;
    public bool $enable_onlineuserbox;
    public bool $enable_newuserbox;
    public bool $enable_didyouknowbox;
    public bool $enable_status_feed;

    public bool $enable_voteforserverbox;
    public ?array $voteforserverbox_content;

    public bool $enable_welcomebox;
    public ?string $welcomebox_content;

    public bool $enable_socialbox;
    public ?string $youtube_url;
    public ?string $facebook_url;
    public ?string $twitter_url;
    public ?string $twitch_url;
    public ?string $tiktok_url;
    public ?string $linkedin_url;
    public ?string $threads_url;
    public ?string $discord_invite_url;

    public bool $enable_discordbox;
    public ?string $discord_server_id;

    public bool $enable_donation_box;
    public ?string $donation_box_url;
    public bool $enable_sticky_header_menu;
    public ?string $header_broadcast_text;
    public ?string $header_broadcast_url;
    public bool $enable_topplayersbox;

    public static function group(): string
    {
        return 'general';
    }
}

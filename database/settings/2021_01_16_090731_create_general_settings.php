<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'MineTrax');
        $this->migrator->add('general.site_header_logo_path_light', '/images/site_header_logo_light.svg');
        $this->migrator->add('general.site_header_logo_path_dark', '/images/site_header_logo_dark.svg');

        $this->migrator->add('general.enable_mcserver_onlineplayersbox', true);
        $this->migrator->add('general.enable_mcserver_statuspingbox', true);
        $this->migrator->add('general.enable_ingamechat', true);
        $this->migrator->add('general.enable_shoutbox', true);
        $this->migrator->add('general.enable_welcomebox', true);
        $this->migrator->add('general.welcomebox_content', '### Hi there 👋. This is WelcomeBox. Change or Disable from General Settings.');
        $this->migrator->add('general.enable_voteforserverbox', false);
        $this->migrator->add('general.voteforserverbox_content', null);
        $this->migrator->add('general.enable_onlineuserbox', true);
        $this->migrator->add('general.enable_newuserbox', true);
        $this->migrator->add('general.enable_didyouknowbox', true);
        $this->migrator->add('general.enable_socialbox', false);
        $this->migrator->add('general.youtube_url', null);
        $this->migrator->add('general.facebook_url', null);
        $this->migrator->add('general.twitter_url', null);
        $this->migrator->add('general.twitch_url', null);
        $this->migrator->add('general.enable_discordbox', false);
        $this->migrator->add('general.discord_server_id', null);
        $this->migrator->add('general.enable_donation_box', false);
        $this->migrator->add('general.donation_box_url', null);
        $this->migrator->add('general.enable_status_feed', true);
    }
}

<?php

use App\Models\User;
use App\Settings\GeneralSettings;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first(); // Super admin from seeder
});

function generalSettingPayload(array $overrides = []): array
{
    return array_merge([
        'site_name' => 'CrazyMC',
        'copyright_name' => null,
        'copyright_url' => null,
        'enable_mcserver_onlineplayersbox' => false,
        'enable_mcserver_statuspingbox' => false,
        'enable_ingamechat' => false,
        'enable_shoutbox' => false,
        'enable_onlineuserbox' => false,
        'enable_newuserbox' => false,
        'enable_didyouknowbox' => false,
        'enable_welcomebox' => false,
        'welcomebox_content' => null,
        'enable_socialbox' => false,
        'youtube_url' => null,
        'facebook_url' => null,
        'twitter_url' => null,
        'twitch_url' => null,
        'tiktok_url' => null,
        'linkedin_url' => null,
        'instagram_url' => null,
        'whatsapp_url' => null,
        'telegram_url' => null,
        'reddit_url' => null,
        'threads_url' => null,
        'github_url' => null,
        'discord_invite_url' => null,
        'enable_discordbox' => false,
        'discord_server_id' => null,
        'enable_voteforserverbox' => false,
        'voteforserverbox_content' => null,
        'enable_donation_box' => false,
        'donation_box_url' => null,
        'enable_status_feed' => false,
        'header_broadcast_text' => null,
        'header_broadcast_url' => null,
        'enable_topplayersbox' => false,
    ], $overrides);
}

test('super admin can view general settings page', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.setting.general.show'))
        ->assertSuccessful();
});

test('super admin can update copyright fields', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.setting.general.update'), generalSettingPayload([
            'copyright_name' => 'Minetrax',
            'copyright_url' => 'https://Minetrax',
        ]))
        ->assertRedirect();

    $settings = app(GeneralSettings::class);

    expect($settings->copyright_name)->toBe('Minetrax')
        ->and($settings->copyright_url)->toBe('https://Minetrax');
});

test('copyright fields can be cleared', function () {
    $settings = app(GeneralSettings::class);
    $settings->copyright_name = 'Minetrax';
    $settings->copyright_url = 'https://Minetrax';
    $settings->save();

    $this->actingAs($this->superAdmin)
        ->post(route('admin.setting.general.update'), generalSettingPayload([
            'copyright_name' => null,
            'copyright_url' => null,
        ]))
        ->assertRedirect();

    $settings = app(GeneralSettings::class);

    expect($settings->copyright_name)->toBeNull()
        ->and($settings->copyright_url)->toBeNull();
});

test('copyright_url must be a valid url when provided', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.setting.general.update'), generalSettingPayload([
            'copyright_url' => 'not-a-url',
        ]))
        ->assertSessionHasErrors('copyright_url');
});

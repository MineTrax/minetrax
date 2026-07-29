<?php

use App\Models\User;

test('profile information can be updated', function () {
    $this->actingAs($user = User::factory()->create());

    $response = $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'show_yob' => true,
        'show_gender' => false,
        'profile_photo_source' => null,
        'dob' => '1995-12-21',
        'gender' => 'f',
        'about' => 'about me',
        's_discord_username' => 'DiscordUser#123',
        's_steam_profile_url' => 'https://steamcommunity.com',
        's_twitter_url' => 'https://twitter.com',
        's_linkedin_url' => 'https://linkedin.com',
        's_tiktok_url' => 'https://tiktok.com',
        's_youtube_url' => 'https://youtube.com',
        's_facebook_url' => 'https://facebook.com',
        's_twitch_url' => 'https://twitch.com',
        's_website_url' => 'https://minecraft.com',
        'cover_image_url' => 'https://google.com/image.png',
        'locale' => null,
    ]);

    expect($user->fresh()->name)->toEqual('Test Name');
    expect($user->fresh()->gender)->toEqual('f');
    expect($user->fresh()->dob->format('Y-m-d'))->toEqual('1995-12-21');
    expect($user->fresh()->about)->toEqual('about me');
});

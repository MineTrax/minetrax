<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated()
    {
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
            's_youtube_url' => 'https://youtube.com',
            's_facebook_url' => 'https://facebook.com',
            's_twitch_url' => 'https://twitch.com',
            's_website_url' => 'https://minecraft.com',
            'cover_image_url' => 'https://google.com/image.png'
        ]);

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('f', $user->fresh()->gender);
        $this->assertEquals('1995-12-21', $user->fresh()->dob->format('Y-m-d'));
        $this->assertEquals('about me', $user->fresh()->about);
    }
}

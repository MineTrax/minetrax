<?php

use App\Models\Player;
use App\Models\User;
use App\Settings\PluginSettings;
use App\Utils\Helpers\CryptoUtils;
use Illuminate\Testing\TestResponse;

function getUserByDiscordId(string $discordId): TestResponse
{
    $uri = "/api/v1/users/{$discordId}";
    $pluginSettings = app(PluginSettings::class);

    return test()->getJson($uri, [
        'X-API-KEY' => $pluginSettings->plugin_api_key,
        'X-SIGNATURE' => CryptoUtils::generateHmacSignature(url($uri), $pluginSettings->plugin_api_secret),
    ]);
}

it('rejects requests without API credentials', function () {
    $this->getJson('/api/v1/users/123456789012345678')->assertUnauthorized();
});

it('returns a user and their linked Minecraft usernames by Discord ID', function () {
    $user = User::factory()->create(['discord_user_id' => '123456789012345678']);
    $xteri = Player::factory()->create(['uuid' => fake()->uuid(), 'username' => 'xteri']);
    $steve = Player::factory()->create(['uuid' => fake()->uuid(), 'username' => 'steve']);
    $user->players()->attach([$xteri->id, $steve->id]);

    getUserByDiscordId('123456789012345678')
        ->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Ok',
            'data' => [
                [
                    'user_id' => $user->id,
                    'linked_users' => [
                        [
                            'player_id' => $xteri->id,
                            'username' => 'xteri',
                        ],
                        [
                            'player_id' => $steve->id,
                            'username' => 'steve',
                        ],
                    ],
                ],
            ],
        ]);
});

it('returns an empty linked users array when the user has no linked players', function () {
    $user = User::factory()->create(['discord_user_id' => '123456789012345678']);

    getUserByDiscordId('123456789012345678')
        ->assertOk()
        ->assertJsonPath('data.0.user_id', $user->id)
        ->assertJsonCount(0, 'data.0.linked_users');
});

it('returns not found for an unknown Discord ID', function () {
    getUserByDiscordId('123456789012345678')
        ->assertNotFound()
        ->assertJson([
            'status' => 'error',
            'type' => 'user_not_found',
        ]);
});

<?php

namespace Tests\Feature;

use App\Models\Player;
use App\Models\Rank;
use App\Models\User;
use App\Settings\PluginSettings;
use App\Utils\Helpers\CryptoUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApiRankMembersTest extends TestCase
{
    use RefreshDatabase;

    private function getJsonWithApiCredentials(string $uri): TestResponse
    {
        $pluginSettings = app(PluginSettings::class);

        return $this->getJson($uri, [
            'X-API-KEY' => $pluginSettings->plugin_api_key,
            'X-SIGNATURE' => CryptoUtils::generateHmacSignature(url($uri), $pluginSettings->plugin_api_secret),
        ]);
    }

    public function test_request_without_api_credentials_is_rejected()
    {
        Rank::factory()->create(['shortname' => 'crazy']);

        $response = $this->getJson('/api/v1/ranks/crazy/members');

        $response->assertStatus(401);
    }

    public function test_signature_is_required_when_signature_validation_enabled()
    {
        Rank::factory()->create(['shortname' => 'crazy']);

        $response = $this->getJson('/api/v1/ranks/crazy/members', [
            'X-API-KEY' => app(PluginSettings::class)->plugin_api_key,
        ]);

        $response->assertStatus(401)
            ->assertJson(['type' => 'signature_missing']);
    }

    public function test_invalid_signature_is_rejected_when_signature_validation_enabled()
    {
        Rank::factory()->create(['shortname' => 'crazy']);

        $response = $this->getJson('/api/v1/ranks/crazy/members', [
            'X-API-KEY' => app(PluginSettings::class)->plugin_api_key,
            'X-SIGNATURE' => 'some-invalid-signature',
        ]);

        $response->assertStatus(401)
            ->assertJson(['type' => 'invalid_signature']);
    }

    public function test_api_key_alone_works_when_signature_validation_disabled()
    {
        config(['minetrax.api_signature_validation' => false]);

        Rank::factory()->create(['shortname' => 'crazy']);

        $response = $this->getJson('/api/v1/ranks/crazy/members', [
            'X-API-KEY' => app(PluginSettings::class)->plugin_api_key,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }

    public function test_it_returns_users_of_given_rank_with_their_discord_id()
    {
        $rank = Rank::factory()->create(['shortname' => 'crazy']);
        $otherRank = Rank::factory()->create(['shortname' => 'other']);

        $userWithDiscord = User::factory()->create(['discord_user_id' => '123456789012345678']);
        $userWithoutDiscord = User::factory()->create(['discord_user_id' => null]);
        $userOfOtherRank = User::factory()->create(['discord_user_id' => '876543210987654321']);

        $userWithDiscord->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $rank->id]));
        $userWithoutDiscord->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $rank->id]));
        $userOfOtherRank->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $otherRank->id]));

        $response = $this->getJsonWithApiCredentials('/api/v1/ranks/crazy/members');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'user_id' => $userWithDiscord->id,
                        'username' => $userWithDiscord->username,
                        'discord_id' => '123456789012345678',
                    ],
                ],
            ]);
    }

    public function test_user_with_multiple_players_of_different_ranks_is_returned_for_each_rank()
    {
        $crazyRank = Rank::factory()->create(['shortname' => 'crazy']);
        $boosterRank = Rank::factory()->create(['shortname' => 'booster']);

        $user = User::factory()->create(['discord_user_id' => '123456789012345678']);
        $user->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $crazyRank->id]));
        $user->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $boosterRank->id]));

        $this->getJsonWithApiCredentials('/api/v1/ranks/crazy/members')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $user->id);

        $this->getJsonWithApiCredentials('/api/v1/ranks/booster/members')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $user->id);
    }

    public function test_user_with_multiple_players_of_same_rank_is_returned_only_once()
    {
        $rank = Rank::factory()->create(['shortname' => 'crazy']);

        $user = User::factory()->create(['discord_user_id' => '123456789012345678']);
        $user->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $rank->id]));
        $user->players()->attach(Player::factory()->create(['uuid' => fake()->uuid(), 'rank_id' => $rank->id]));

        $this->getJsonWithApiCredentials('/api/v1/ranks/crazy/members')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_returns_empty_list_when_rank_has_no_members()
    {
        Rank::factory()->create(['shortname' => 'crazy']);

        $response = $this->getJsonWithApiCredentials('/api/v1/ranks/crazy/members');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_it_returns_404_for_unknown_rank()
    {
        $response = $this->getJsonWithApiCredentials('/api/v1/ranks/unknown-rank/members');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'type' => 'rank_not_found',
            ]);
    }
}

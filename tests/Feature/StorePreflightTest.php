<?php

namespace Tests\Feature;

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueueJob;
use App\Models\CommandQueue;
use App\Models\Player;
use App\Models\Server;
use App\Services\MinecraftApiService;
use App\Utils\Helpers\MinecraftUuidUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers the pre-flight fixes the Store module depends on: usable factories, a `config` column
 * wide enough for store payloads, a command job that tolerates config without the online flag,
 * UUID conversion between Mojang's and MineTrax's formats, and a username lookup that does not
 * cache failures.
 */
class StorePreflightTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_factory_creates_a_valid_player()
    {
        $player = Player::factory()->create();

        $this->assertNotNull($player->uuid);
        $this->assertTrue(Str::isUuid($player->uuid));
        $this->assertNotNull($player->username);
        $this->assertDatabaseHas('players', ['uuid' => $player->uuid]);
    }

    public function test_player_factory_can_create_many_players_without_uuid_collision()
    {
        $players = Player::factory()->count(5)->create();

        $this->assertCount(5, $players->pluck('uuid')->unique());
    }

    public function test_server_factory_sets_a_webquery_port_by_default()
    {
        $this->assertNotNull(Server::factory()->create()->webquery_port);
    }

    public function test_server_factory_webquery_port_can_still_be_overridden_to_null()
    {
        $this->assertNull(Server::factory()->create(['webquery_port' => null])->webquery_port);
    }

    public function test_command_queue_config_survives_a_payload_larger_than_255_characters()
    {
        $server = Server::factory()->create();

        $config = [
            'is_player_online_required' => true,
            'store_note' => str_repeat('a', 500),
        ];

        $commandQueue = CommandQueue::create([
            'server_id' => $server->id,
            'parsed_command' => 'say hello',
            'config' => $config,
            'status' => CommandQueueStatus::PENDING,
            'max_attempts' => 3,
        ]);

        $this->assertEquals($config, $commandQueue->fresh()->config);
        $this->assertEquals(500, \strlen($commandQueue->fresh()->config['store_note']));
    }

    public function test_run_command_queue_job_does_not_error_when_config_omits_the_online_flag()
    {
        // A server without a webquery port short-circuits before any socket work, which lets us
        // assert the config access itself no longer throws on a missing key.
        $server = Server::factory()->create(['webquery_port' => null]);

        $commandQueue = CommandQueue::create([
            'server_id' => $server->id,
            'parsed_command' => 'say hello',
            'config' => ['some_other_key' => true],
            'status' => CommandQueueStatus::PENDING,
            'max_attempts' => 3,
        ]);

        (new RunCommandQueueJob($commandQueue))->handle();

        $this->assertEquals(CommandQueueStatus::CANCELLED, $commandQueue->fresh()->status);
    }

    public function test_uuid_can_be_converted_to_dashed_form()
    {
        $this->assertEquals(
            '069a79f4-44e9-4726-a5be-fca90e38aaf5',
            MinecraftUuidUtils::toDashed('069a79f444e94726a5befca90e38aaf5')
        );
    }

    public function test_already_dashed_uuid_is_returned_unchanged()
    {
        $this->assertEquals(
            '069a79f4-44e9-4726-a5be-fca90e38aaf5',
            MinecraftUuidUtils::toDashed('069A79F4-44E9-4726-A5BE-FCA90E38AAF5')
        );
    }

    public function test_invalid_uuid_input_returns_null_rather_than_a_malformed_uuid()
    {
        $this->assertNull(MinecraftUuidUtils::toDashed('not-a-uuid'));
        $this->assertNull(MinecraftUuidUtils::toDashed(''));
        $this->assertNull(MinecraftUuidUtils::toDashed(null));
    }

    public function test_uuid_can_be_converted_to_undashed_form()
    {
        $this->assertEquals(
            '069a79f444e94726a5befca90e38aaf5',
            MinecraftUuidUtils::toUndashed('069a79f4-44e9-4726-a5be-fca90e38aaf5')
        );
    }

    public function test_offline_uuid_matches_the_value_minecraft_servers_compute()
    {
        // Golden vector: the widely published offline-mode UUID for "Notch", as produced by
        // Java's UUID.nameUUIDFromBytes("OfflinePlayer:Notch").
        $this->assertEquals('b50ad385-829d-3141-a216-7e7d7539ba7f', MinecraftUuidUtils::offlineUuid('Notch'));
    }

    public function test_offline_uuid_is_deterministic_and_username_specific()
    {
        $this->assertEquals(
            MinecraftUuidUtils::offlineUuid('SomePlayer'),
            MinecraftUuidUtils::offlineUuid('SomePlayer')
        );

        $this->assertNotEquals(
            MinecraftUuidUtils::offlineUuid('SomePlayer'),
            MinecraftUuidUtils::offlineUuid('someplayer')
        );
    }

    public function test_offline_uuid_is_a_valid_version_3_dashed_uuid()
    {
        $uuid = MinecraftUuidUtils::offlineUuid('SomePlayer');

        $this->assertTrue(Str::isUuid($uuid));
        $this->assertEquals('3', $uuid[14], 'Offline UUIDs must carry version 3.');
        $this->assertContains($uuid[19], ['8', '9', 'a', 'b'], 'Offline UUIDs must carry the RFC 4122 variant.');
    }

    public function test_username_lookup_returns_uuid_and_caches_it()
    {
        Cache::flush();
        Http::fake([
            'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5', 'name' => 'Notch']),
        ]);

        $this->assertEquals('069a79f444e94726a5befca90e38aaf5', MinecraftApiService::playerUsernameToUuid('Notch'));
        $this->assertEquals('069a79f444e94726a5befca90e38aaf5', Cache::get('minecraft:uuid:Notch'));
    }

    public function test_failed_username_lookup_is_not_cached()
    {
        Cache::flush();
        Http::fake([
            'api.minecraftservices.com/*' => Http::sequence()
                ->push(['errorMessage' => 'Not found'], 404)
                ->push(['id' => '069a79f444e94726a5befca90e38aaf5'], 200),
        ]);

        $this->assertNull(MinecraftApiService::playerUsernameToUuid('GhostPlayer'));
        $this->assertNull(Cache::get('minecraft:uuid:GhostPlayer'));

        // A username that registers later must resolve rather than stay poisoned by the 404.
        $this->assertEquals('069a79f444e94726a5befca90e38aaf5', MinecraftApiService::playerUsernameToUuid('GhostPlayer'));
    }
}

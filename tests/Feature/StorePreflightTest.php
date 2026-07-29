<?php

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

uses(RefreshDatabase::class);

test('player factory creates a valid player', function () {
    $player = Player::factory()->create();

    expect($player->uuid)->not->toBeNull();
    expect(Str::isUuid($player->uuid))->toBeTrue();
    expect($player->username)->not->toBeNull();
    $this->assertDatabaseHas('players', ['uuid' => $player->uuid]);
});

test('player factory can create many players without uuid collision', function () {
    $players = Player::factory()->count(5)->create();

    expect($players->pluck('uuid')->unique())->toHaveCount(5);
});

test('server factory sets a webquery port by default', function () {
    expect(Server::factory()->create()->webquery_port)->not->toBeNull();
});

test('server factory webquery port can still be overridden to null', function () {
    expect(Server::factory()->create(['webquery_port' => null])->webquery_port)->toBeNull();
});

test('command queue config survives a payload larger than 255 characters', function () {
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

    expect($commandQueue->fresh()->config)->toEqual($config);
    expect(\strlen($commandQueue->fresh()->config['store_note']))->toEqual(500);
});

test('run command queue job does not error when config omits the online flag', function () {
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

    expect($commandQueue->fresh()->status)->toEqual(CommandQueueStatus::CANCELLED);
});

test('uuid can be converted to dashed form', function () {
    expect(MinecraftUuidUtils::toDashed('069a79f444e94726a5befca90e38aaf5'))->toEqual('069a79f4-44e9-4726-a5be-fca90e38aaf5');
});

test('already dashed uuid is returned unchanged', function () {
    expect(MinecraftUuidUtils::toDashed('069A79F4-44E9-4726-A5BE-FCA90E38AAF5'))->toEqual('069a79f4-44e9-4726-a5be-fca90e38aaf5');
});

test('invalid uuid input returns null rather than a malformed uuid', function () {
    expect(MinecraftUuidUtils::toDashed('not-a-uuid'))->toBeNull();
    expect(MinecraftUuidUtils::toDashed(''))->toBeNull();
    expect(MinecraftUuidUtils::toDashed(null))->toBeNull();
});

test('uuid can be converted to undashed form', function () {
    expect(MinecraftUuidUtils::toUndashed('069a79f4-44e9-4726-a5be-fca90e38aaf5'))->toEqual('069a79f444e94726a5befca90e38aaf5');
});

test('offline uuid matches the value minecraft servers compute', function () {
    // Golden vector: the widely published offline-mode UUID for "Notch", as produced by
    // Java's UUID.nameUUIDFromBytes("OfflinePlayer:Notch").
    expect(MinecraftUuidUtils::offlineUuid('Notch'))->toEqual('b50ad385-829d-3141-a216-7e7d7539ba7f');
});

test('offline uuid is deterministic and username specific', function () {
    expect(MinecraftUuidUtils::offlineUuid('SomePlayer'))->toEqual(MinecraftUuidUtils::offlineUuid('SomePlayer'));

    $this->assertNotEquals(
        MinecraftUuidUtils::offlineUuid('SomePlayer'),
        MinecraftUuidUtils::offlineUuid('someplayer')
    );
});

test('offline uuid is a valid version 3 dashed uuid', function () {
    $uuid = MinecraftUuidUtils::offlineUuid('SomePlayer');

    expect(Str::isUuid($uuid))->toBeTrue();
    expect($uuid[14])->toEqual('3', 'Offline UUIDs must carry version 3.');
    $this->assertContains($uuid[19], ['8', '9', 'a', 'b'], 'Offline UUIDs must carry the RFC 4122 variant.');
});

test('username lookup returns uuid and caches it', function () {
    Cache::flush();
    Http::fake([
        'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5', 'name' => 'Notch']),
    ]);

    expect(MinecraftApiService::playerUsernameToUuid('Notch'))->toEqual('069a79f444e94726a5befca90e38aaf5');
    expect(Cache::get('minecraft:uuid:Notch'))->toEqual('069a79f444e94726a5befca90e38aaf5');
});

test('failed username lookup is not cached', function () {
    Cache::flush();
    Http::fake([
        'api.minecraftservices.com/*' => Http::sequence()
            ->push(['errorMessage' => 'Not found'], 404)
            ->push(['id' => '069a79f444e94726a5befca90e38aaf5'], 200),
    ]);

    expect(MinecraftApiService::playerUsernameToUuid('GhostPlayer'))->toBeNull();
    expect(Cache::get('minecraft:uuid:GhostPlayer'))->toBeNull();

    // A username that registers later must resolve rather than stay poisoned by the 404.
    expect(MinecraftApiService::playerUsernameToUuid('GhostPlayer'))->toEqual('069a79f444e94726a5befca90e38aaf5');
});

<?php

use App\Jobs\ResyncPlayersTableJob;
use App\Models\MinecraftPlayer;
use App\Models\Player;
use App\Models\Server;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Support\Facades\Event;

/**
 * @param  array<string, mixed>  $attributes
 */
function createMinecraftPlayer(Server $server, string $playerUuid, array $attributes = []): MinecraftPlayer
{
    return MinecraftPlayer::query()->create([
        'player_uuid' => $playerUuid,
        'player_username' => 'Steve',
        'server_id' => $server->id,
        'first_seen_at' => now()->subDays(5),
        'last_seen_at' => now()->subDay(),
        ...$attributes,
    ]);
}

test('it aggregates minecraft player stats from all servers into the players table', function () {
    $playerUuid = fake()->uuid();
    $firstServer = Server::factory()->create();
    $secondServer = Server::factory()->create();
    Player::factory()->create(['uuid' => $playerUuid, 'username' => 'OldName']);

    createMinecraftPlayer($firstServer, $playerUuid, [
        'player_username' => 'OldName',
        'mob_kills' => 10,
        'play_time' => 100,
        'first_seen_at' => '2025-01-01 00:00:00',
        'last_seen_at' => '2025-02-01 00:00:00',
    ]);
    createMinecraftPlayer($secondServer, $playerUuid, [
        'player_username' => 'NewName',
        'mob_kills' => 5,
        'play_time' => 50,
        'first_seen_at' => '2025-01-15 00:00:00',
        'last_seen_at' => '2025-03-01 00:00:00',
    ]);

    (new ResyncPlayersTableJob)->handle();

    $player = Player::query()->where('uuid', $playerUuid)->sole();
    expect($player->username)->toBe('NewName')
        ->and((int) $player->total_mob_kills)->toBe(15)
        ->and((int) $player->play_time)->toBe(150)
        ->and($player->first_seen_at->toDateTimeString())->toBe('2025-01-01 00:00:00')
        ->and($player->last_seen_at->toDateTimeString())->toBe('2025-03-01 00:00:00');
});

test('it deletes players that no longer have any minecraft player records', function () {
    $server = Server::factory()->create();
    $keptPlayerUuid = fake()->uuid();
    $orphanedPlayerUuid = fake()->uuid();
    Player::factory()->create(['uuid' => $keptPlayerUuid]);
    Player::factory()->create(['uuid' => $orphanedPlayerUuid]);
    createMinecraftPlayer($server, $keptPlayerUuid);

    (new ResyncPlayersTableJob)->handle();

    expect(Player::query()->pluck('uuid')->all())->toBe([$keptPlayerUuid]);
});

test('it does not wrap the resync in a long running transaction', function () {
    $server = Server::factory()->create();
    $playerUuid = fake()->uuid();
    Player::factory()->create(['uuid' => $playerUuid]);
    createMinecraftPlayer($server, $playerUuid);

    Event::fake([TransactionBeginning::class]);

    (new ResyncPlayersTableJob)->handle();

    Event::assertNotDispatched(TransactionBeginning::class);
});

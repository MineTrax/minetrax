<?php

use App\Models\Player;
use App\Models\StoreBan;
use App\Models\User;
use App\Services\StoreBanService;
use App\Services\StorePlayerResolver;
use App\Settings\StoreSettings;
use App\Utils\Helpers\MinecraftUuidUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    Cache::flush();
    $this->resolver = app(StorePlayerResolver::class);
});

function setMojangVerification(bool $enabled): void
{
    $settings = app(StoreSettings::class);
    $settings->mojang_username_verification = $enabled;
    $settings->save();

    test()->resolver = app(StorePlayerResolver::class);
}

test('a known player is resolved without any external lookup', function () {
    Http::fake();
    // any outbound call would be an unexpected dependency
    $player = Player::factory()->create(['username' => 'Steve']);

    $result = $this->resolver->resolve('Steve');

    expect($result['player']->id)->toEqual($player->id);
    expect($result['uuid'])->toEqual($player->uuid);
    Http::assertNothingSent();
});

test('a known player is matched case insensitively', function () {
    $player = Player::factory()->create(['username' => 'Steve']);

    expect($this->resolver->resolve('sTeVe')['player']->id)->toEqual($player->id);
});

test('a linked player of the buyer is preferred', function () {
    $user = User::factory()->create();
    $player = Player::factory()->create(['username' => 'Alex']);
    $user->players()->attach($player);

    $result = $this->resolver->resolve('Alex', $user->fresh());

    expect($result['player']->id)->toEqual($player->id);
});

test('an unknown username is resolved through mojang and dashed', function () {
    setMojangVerification(true);
    Http::fake([
        'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5', 'name' => 'Notch']),
    ]);

    $result = $this->resolver->resolve('Notch');

    // Mojang returns undashed; players.uuid is a dashed char(36).
    expect($result['uuid'])->toEqual('069a79f4-44e9-4726-a5be-fca90e38aaf5');
    expect(Str::isUuid($result['uuid']))->toBeTrue();
    expect($result['player'])->toBeNull('A player row is not invented for someone unknown to the site.');
    expect($result['username'])->toEqual('Notch');
});

test('a mojang lookup can match a player who has since been renamed', function () {
    setMojangVerification(true);
    $player = Player::factory()->create([
        'uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        'username' => 'NewName',
    ]);
    Http::fake([
        'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5']),
    ]);

    $result = $this->resolver->resolve('OldName');

    expect($result['player']->id)->toEqual($player->id);
    expect($result['username'])->toEqual('NewName', 'The current username wins over the one typed.');
});

test('an unknown mojang username is rejected', function () {
    setMojangVerification(true);
    Http::fake(['api.minecraftservices.com/*' => Http::response(['errorMessage' => 'Not found'], 404)]);

    $this->expectException(ValidationException::class);
    $this->resolver->resolve('DefinitelyNotReal');
});

test('offline mode derives the uuid the server would use', function () {
    setMojangVerification(false);
    Http::fake();

    $result = $this->resolver->resolve('Notch');

    expect($result['uuid'])->toEqual(MinecraftUuidUtils::offlineUuid('Notch'));
    expect($result['uuid'])->toEqual('b50ad385-829d-3141-a216-7e7d7539ba7f');
    Http::assertNothingSent();
});

test('offline mode still validates the username shape', function () {
    setMojangVerification(false);

    $this->expectException(ValidationException::class);
    $this->resolver->resolve('not a valid name!');
});

test('an empty username is rejected', function () {
    $this->expectException(ValidationException::class);
    $this->resolver->resolve('   ');
});

test('a ban matches on player uuid', function () {
    $bans = app(StoreBanService::class);
    StoreBan::factory()->create(['player_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee']);

    expect($bans->isBanned(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', null, null))->toBeTrue();
    expect($bans->isBanned(null, 'ffffffff-bbbb-cccc-dddd-eeeeeeeeeeee', null, null))->toBeFalse();
});

test('a ban matches on ip or email so a guest cannot simply return', function () {
    $bans = app(StoreBanService::class);
    StoreBan::factory()->create(['player_uuid' => null, 'ip_address' => '203.0.113.7']);
    StoreBan::factory()->create(['player_uuid' => null, 'email' => 'bad@example.com']);

    expect($bans->isBanned(null, null, '203.0.113.7', null))->toBeTrue();
    expect($bans->isBanned(null, null, null, 'BAD@EXAMPLE.COM'))->toBeTrue();
});

test('a ban matches on user', function () {
    $bans = app(StoreBanService::class);
    $user = User::factory()->create();
    StoreBan::factory()->create(['user_id' => $user->id, 'player_uuid' => null]);

    expect($bans->isBanned($user, null, null, null))->toBeTrue();
});

test('an expired ban does not block', function () {
    $bans = app(StoreBanService::class);
    StoreBan::factory()->create(['player_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', 'expires_at' => now()->subDay()]);

    expect($bans->isBanned(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', null, null))->toBeFalse();
});

test('an all null identity never matches', function () {
    // Guards against a null column silently matching a null lookup and banning everyone.
    $bans = app(StoreBanService::class);
    StoreBan::factory()->create(['player_uuid' => null, 'ip_address' => null, 'email' => null]);

    expect($bans->isBanned(null, null, null, null))->toBeFalse();
});

test('a chargeback ban is flagged automatic', function () {
    $ban = app(StoreBanService::class)->banForChargeback(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '203.0.113.7', 'x@example.com', 'Chargeback on order 1');

    expect($ban->is_automatic)->toBeTrue();
    expect($ban->expires_at)->toBeNull('A chargeback ban is permanent until an admin lifts it.');
});

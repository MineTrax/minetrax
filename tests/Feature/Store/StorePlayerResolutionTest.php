<?php

namespace Tests\Feature\Store;

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
use Tests\TestCase;

class StorePlayerResolutionTest extends TestCase
{
    use RefreshDatabase;

    private StorePlayerResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        Cache::flush();
        $this->resolver = app(StorePlayerResolver::class);
    }

    private function setMojangVerification(bool $enabled): void
    {
        $settings = app(StoreSettings::class);
        $settings->mojang_username_verification = $enabled;
        $settings->save();

        $this->resolver = app(StorePlayerResolver::class);
    }

    public function test_a_known_player_is_resolved_without_any_external_lookup()
    {
        Http::fake(); // any outbound call would be an unexpected dependency
        $player = Player::factory()->create(['username' => 'Steve']);

        $result = $this->resolver->resolve('Steve');

        $this->assertEquals($player->id, $result['player']->id);
        $this->assertEquals($player->uuid, $result['uuid']);
        Http::assertNothingSent();
    }

    public function test_a_known_player_is_matched_case_insensitively()
    {
        $player = Player::factory()->create(['username' => 'Steve']);

        $this->assertEquals($player->id, $this->resolver->resolve('sTeVe')['player']->id);
    }

    public function test_a_linked_player_of_the_buyer_is_preferred()
    {
        $user = User::factory()->create();
        $player = Player::factory()->create(['username' => 'Alex']);
        $user->players()->attach($player);

        $result = $this->resolver->resolve('Alex', $user->fresh());

        $this->assertEquals($player->id, $result['player']->id);
    }

    public function test_an_unknown_username_is_resolved_through_mojang_and_dashed()
    {
        $this->setMojangVerification(true);
        Http::fake([
            'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5', 'name' => 'Notch']),
        ]);

        $result = $this->resolver->resolve('Notch');

        // Mojang returns undashed; players.uuid is a dashed char(36).
        $this->assertEquals('069a79f4-44e9-4726-a5be-fca90e38aaf5', $result['uuid']);
        $this->assertTrue(Str::isUuid($result['uuid']));
        $this->assertNull($result['player'], 'A player row is not invented for someone unknown to the site.');
        $this->assertEquals('Notch', $result['username']);
    }

    public function test_a_mojang_lookup_can_match_a_player_who_has_since_been_renamed()
    {
        $this->setMojangVerification(true);
        $player = Player::factory()->create([
            'uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
            'username' => 'NewName',
        ]);
        Http::fake([
            'api.minecraftservices.com/*' => Http::response(['id' => '069a79f444e94726a5befca90e38aaf5']),
        ]);

        $result = $this->resolver->resolve('OldName');

        $this->assertEquals($player->id, $result['player']->id);
        $this->assertEquals('NewName', $result['username'], 'The current username wins over the one typed.');
    }

    public function test_an_unknown_mojang_username_is_rejected()
    {
        $this->setMojangVerification(true);
        Http::fake(['api.minecraftservices.com/*' => Http::response(['errorMessage' => 'Not found'], 404)]);

        $this->expectException(ValidationException::class);
        $this->resolver->resolve('DefinitelyNotReal');
    }

    public function test_offline_mode_derives_the_uuid_the_server_would_use()
    {
        $this->setMojangVerification(false);
        Http::fake();

        $result = $this->resolver->resolve('Notch');

        $this->assertEquals(MinecraftUuidUtils::offlineUuid('Notch'), $result['uuid']);
        $this->assertEquals('b50ad385-829d-3141-a216-7e7d7539ba7f', $result['uuid']);
        Http::assertNothingSent();
    }

    public function test_offline_mode_still_validates_the_username_shape()
    {
        $this->setMojangVerification(false);

        $this->expectException(ValidationException::class);
        $this->resolver->resolve('not a valid name!');
    }

    public function test_an_empty_username_is_rejected()
    {
        $this->expectException(ValidationException::class);
        $this->resolver->resolve('   ');
    }

    // --- Bans -----------------------------------------------------------------------------

    public function test_a_ban_matches_on_player_uuid()
    {
        $bans = app(StoreBanService::class);
        StoreBan::factory()->create(['player_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee']);

        $this->assertTrue($bans->isBanned(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', null, null));
        $this->assertFalse($bans->isBanned(null, 'ffffffff-bbbb-cccc-dddd-eeeeeeeeeeee', null, null));
    }

    public function test_a_ban_matches_on_ip_or_email_so_a_guest_cannot_simply_return()
    {
        $bans = app(StoreBanService::class);
        StoreBan::factory()->create(['player_uuid' => null, 'ip_address' => '203.0.113.7']);
        StoreBan::factory()->create(['player_uuid' => null, 'email' => 'bad@example.com']);

        $this->assertTrue($bans->isBanned(null, null, '203.0.113.7', null));
        $this->assertTrue($bans->isBanned(null, null, null, 'BAD@EXAMPLE.COM'));
    }

    public function test_a_ban_matches_on_user()
    {
        $bans = app(StoreBanService::class);
        $user = User::factory()->create();
        StoreBan::factory()->create(['user_id' => $user->id, 'player_uuid' => null]);

        $this->assertTrue($bans->isBanned($user, null, null, null));
    }

    public function test_an_expired_ban_does_not_block()
    {
        $bans = app(StoreBanService::class);
        StoreBan::factory()->create(['player_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', 'expires_at' => now()->subDay()]);

        $this->assertFalse($bans->isBanned(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', null, null));
    }

    public function test_an_all_null_identity_never_matches()
    {
        // Guards against a null column silently matching a null lookup and banning everyone.
        $bans = app(StoreBanService::class);
        StoreBan::factory()->create(['player_uuid' => null, 'ip_address' => null, 'email' => null]);

        $this->assertFalse($bans->isBanned(null, null, null, null));
    }

    public function test_a_chargeback_ban_is_flagged_automatic()
    {
        $ban = app(StoreBanService::class)->banForChargeback(null, 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee', '203.0.113.7', 'x@example.com', 'Chargeback on order 1');

        $this->assertTrue($ban->is_automatic);
        $this->assertNull($ban->expires_at, 'A chargeback ban is permanent until an admin lifts it.');
    }
}

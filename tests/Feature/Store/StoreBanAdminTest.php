<?php

use App\Models\StoreBan;
use App\Models\User;
use App\Services\StoreBanService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function banAdminValidPayload(array $overrides = []): array
{
    return array_merge([
        'username' => null,
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        'ip_address' => null,
        'email' => null,
        'reason' => 'Chargeback abuse',
        'expires_at' => null,
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.ban.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.ban.index'))->assertStatus(302);
});

test('staff without the permission are forbidden', function () {
    // Moderator is staff but is granted no store permissions by RoleSeeder.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.ban.index'))->assertStatus(403);
});

test('superadmin can list bans', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreBan::factory()->create();

    $this->get(route('admin.store.ban.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreBan/IndexStoreBan')
            ->has('bans.data', 1)
        );
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    // Superadmin bypasses the policy gate, so a permissioned non-superadmin proves the gate.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.ban.index'))->assertStatus(403);
});

test('admin can create a ban', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload())
        ->assertRedirect(route('admin.store.ban.index'));

    $this->assertDatabaseHas('store_bans', [
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        'reason' => 'Chargeback abuse',
        'is_automatic' => false,
        'created_by' => 1,
    ]);
});

test('a ban created here is never marked automatic', function () {
    // The listing separates a staff decision from a chargeback's, so the flag cannot be settable.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload(['is_automatic' => true]));

    expect(StoreBan::first()->is_automatic)->toBeFalse();
});

test('an account is banned by username', function () {
    $this->actingAs(User::whereId(1)->first());
    $target = User::factory()->create(['username' => 'refundhunter']);

    $this->post(route('admin.store.ban.store'), banAdminValidPayload([
        'username' => 'refundhunter',
        'player_uuid' => null,
    ]))->assertRedirect(route('admin.store.ban.index'));

    $this->assertDatabaseHas('store_bans', ['user_id' => $target->id, 'player_uuid' => null]);
});

test('an unknown username is refused', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload(['username' => 'nobody-here']))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseCount('store_bans', 0);
});

test('a ban with no identity at all is refused', function () {
    // Such a row matches nothing, so storing it would look like a ban and block nobody.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload(['player_uuid' => null]))
        ->assertSessionHasErrors('username');

    $this->assertDatabaseCount('store_bans', 0);
});

test('an undashed uuid is stored in the dashed form the orders use', function () {
    // Mojang hands out the 32-char form; store_orders.player_uuid is dashed char(36). A ban stored
    // in the other format would match nobody.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload([
        'player_uuid' => '069a79f444e94726a5befca90e38aaf5',
    ]))->assertRedirect(route('admin.store.ban.index'));

    $this->assertDatabaseHas('store_bans', ['player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5']);
});

test('an email is stored lowercase', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload([
        'player_uuid' => null,
        'email' => ' Buyer@Example.COM ',
    ]))->assertRedirect(route('admin.store.ban.index'));

    $this->assertDatabaseHas('store_bans', ['email' => 'buyer@example.com']);
});

test('an invalid ip is refused', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload(['ip_address' => 'not-an-ip']))
        ->assertSessionHasErrors('ip_address');
});

test('an expiry in the past is refused on create', function () {
    // It would store a ban that blocks nobody the moment it is saved.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload([
        'expires_at' => now()->subDay()->toDateTimeString(),
    ]))->assertSessionHasErrors('expires_at');
});

test('a created ban actually blocks the identity', function () {
    // The point of the screen: the service is what checkout consults.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.ban.store'), banAdminValidPayload());

    expect(app(StoreBanService::class)->isBanned(null, '069a79f4-44e9-4726-a5be-fca90e38aaf5', null, null))->toBeTrue();
});

test('admin can edit a ban', function () {
    $this->actingAs(User::whereId(1)->first());
    $ban = StoreBan::factory()->create();

    $this->get(route('admin.store.ban.edit', $ban->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreBan/EditStoreBan')
            ->where('storeBan.id', $ban->id)
        );
});

test('the edit form receives the banned account as a username', function () {
    $this->actingAs(User::whereId(1)->first());
    $target = User::factory()->create(['username' => 'refundhunter']);
    $ban = StoreBan::factory()->create(['user_id' => $target->id]);

    $this->get(route('admin.store.ban.edit', $ban->id))
        ->assertInertia(fn ($page) => $page->where('username', 'refundhunter'));
});

test('editing a ban does not change how it was raised', function () {
    // A chargeback ban stays a chargeback ban however much staff correct its identities.
    $this->actingAs(User::whereId(1)->first());
    $ban = StoreBan::factory()->create(['is_automatic' => true, 'created_by' => null]);

    $this->put(route('admin.store.ban.update', $ban->id), banAdminValidPayload([
        'reason' => 'Corrected reason',
    ]))->assertRedirect(route('admin.store.ban.index'));

    $ban->refresh();
    expect($ban->is_automatic)->toBeTrue();
    expect($ban->reason)->toBe('Corrected reason');
    expect($ban->created_by)->toBeNull();
});

test('a ban can be expired by hand to lift it while keeping the record', function () {
    // The alternative to deleting: the row survives as history and stops blocking.
    $this->actingAs(User::whereId(1)->first());
    $ban = StoreBan::factory()->create(['player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5']);

    $this->put(route('admin.store.ban.update', $ban->id), banAdminValidPayload([
        'expires_at' => now()->subMinute()->toDateTimeString(),
    ]))->assertRedirect(route('admin.store.ban.index'));

    expect(app(StoreBanService::class)->isBanned(null, '069a79f4-44e9-4726-a5be-fca90e38aaf5', null, null))->toBeFalse();
    $this->assertDatabaseHas('store_bans', ['id' => $ban->id]);
});

test('admin can lift a ban by deleting it', function () {
    $this->actingAs(User::whereId(1)->first());
    $ban = StoreBan::factory()->create();

    $this->delete(route('admin.store.ban.delete', $ban->id))
        ->assertRedirect(route('admin.store.ban.index'));

    $this->assertDatabaseMissing('store_bans', ['id' => $ban->id]);
});

test('the listing can be narrowed to bans still in force', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreBan::factory()->create(['reason' => 'still blocking']);
    StoreBan::factory()->create(['reason' => 'lapsed', 'expires_at' => now()->subDay()]);

    $this->get(route('admin.store.ban.index', ['filter' => ['active' => 'true']]))
        ->assertInertia(fn ($page) => $page
            ->has('bans.data', 1)
            ->where('bans.data.0.reason', 'still blocking')
        );

    $this->get(route('admin.store.ban.index', ['filter' => ['active' => 'false']]))
        ->assertInertia(fn ($page) => $page
            ->has('bans.data', 1)
            ->where('bans.data.0.reason', 'lapsed')
        );
});

test('the listing can be searched across every identity', function () {
    $this->actingAs(User::whereId(1)->first());
    $target = User::factory()->create(['username' => 'refundhunter']);
    StoreBan::factory()->create(['user_id' => $target->id, 'player_uuid' => null]);
    StoreBan::factory()->create(['player_uuid' => null, 'ip_address' => '203.0.113.9']);
    StoreBan::factory()->create(['player_uuid' => null, 'email' => 'buyer@example.com']);

    foreach (['refundhunter', '203.0.113.9', 'buyer@example.com'] as $needle) {
        $this->get(route('admin.store.ban.index', ['filter' => ['q' => $needle]]))
            ->assertInertia(fn ($page) => $page->has('bans.data', 1));
    }
});

test('a permissioned non superadmin can manage bans', function () {
    // RoleSeeder grants the admin role the four store_bans permissions.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.ban.index'))->assertStatus(200);
    $this->actingAs($staff)->post(route('admin.store.ban.store'), banAdminValidPayload())
        ->assertRedirect(route('admin.store.ban.index'));
});

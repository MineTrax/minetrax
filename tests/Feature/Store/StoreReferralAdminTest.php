<?php

use App\Enums\StoreReferralAttributionMode;
use App\Models\Server;
use App\Models\StoreCommand;
use App\Models\StoreCoupon;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function referralPayload(array $overrides = []): array
{
    return array_merge([
        'code' => 'KAKAMORA',
        'referrer_name' => 'Kakamora',
        'username' => null,
        'share_bp' => 500,
        'store_coupon_id' => null,
        'is_url_tracking_enabled' => true,
        'attribution_window_days' => 3,
        'attribution_mode' => StoreReferralAttributionMode::FIRST_TOUCH->value,
        'is_command_execution_enabled' => false,
        'is_enabled' => true,
        'notes' => null,
        'commands' => [],
    ], $overrides);
}

/**
 * One row of the command repeater, in the shape the Vue form submits.
 *
 * @param  array<int, int>  $serverIds
 */
function referralCommandPayload(array $overrides = [], array $serverIds = []): array
{
    return array_merge([
        'command' => 'say thanks {REFERRER_NAME}',
        'delay_seconds' => 0,
        'is_player_online_required' => false,
        'sort_order' => 0,
        'servers' => array_map(fn (int $id) => ['id' => $id], $serverIds),
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.referral.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.referral.index'))->assertStatus(302);
});

test('staff without the permission are forbidden', function () {
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.referral.index'))->assertStatus(403);
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.referral.index'))->assertStatus(403);
});

test('superadmin can list referrals', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreReferral::factory()->create(['code' => 'LISTED']);

    $this->get(route('admin.store.referral.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreReferral/IndexStoreReferral')
            ->has('referrals.data', 1)
            ->where('referrals.data.0.code', 'LISTED')
        );
});

test('a code is uppercased and stripped of whitespace', function () {
    // Buyers copy these off a video description by hand.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload(['code' => ' kaka mora ']))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_referrals', ['code' => 'KAKAMORA']);
});

test('a code with punctuation is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload(['code' => 'KAKA!MORA']))
        ->assertSessionHasErrors(['code']);
});

test('a duplicate code is rejected, but a deleted one frees its code', function () {
    $this->actingAs(User::whereId(1)->first());
    $existing = StoreReferral::factory()->create(['code' => 'TAKEN']);

    $this->post(route('admin.store.referral.store'), referralPayload(['code' => 'TAKEN']))
        ->assertSessionHasErrors(['code']);

    // Soft-deleted rows do not hold their code hostage — the same trap the package slug hit.
    $existing->delete();

    $this->post(route('admin.store.referral.store'), referralPayload(['code' => 'TAKEN']))
        ->assertSessionHasNoErrors();
});

test('a share above one hundred percent is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload(['share_bp' => 10001]))
        ->assertSessionHasErrors(['share_bp']);
});

test('a zero share is allowed, for a code that only tracks', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload(['share_bp' => 0]))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_referrals', ['code' => 'KAKAMORA', 'share_bp' => 0]);
});

test('an empty window is stored as a lifetime rather than a zero', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload(['attribution_window_days' => '']))
        ->assertSessionHasNoErrors();

    expect(StoreReferral::first()->attribution_window_days)->toBeNull();
});

test('a linked account is named by username, and an unknown one is rejected', function () {
    $this->actingAs(User::whereId(1)->first());
    $member = User::factory()->create(['username' => 'kakamora']);

    $this->post(route('admin.store.referral.store'), referralPayload(['username' => 'nobody-here']))
        ->assertSessionHasErrors(['username']);

    $this->post(route('admin.store.referral.store'), referralPayload(['username' => 'kakamora']))
        ->assertSessionHasNoErrors();

    expect(StoreReferral::first()->user_id)->toBe($member->id);
});

test('an attached coupon is recorded', function () {
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->stackable()->create();

    $this->post(route('admin.store.referral.store'), referralPayload(['store_coupon_id' => $coupon->id]))
        ->assertSessionHasNoErrors();

    expect(StoreReferral::first()->coupon->is($coupon))->toBeTrue();
});

test('a referral reward has to be a stackable coupon', function () {
    // An exclusive one would displace whatever voucher the buyer already held, so the reward for
    // using a creator code would cost them their own — the opposite of an incentive.
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->create();

    $this->post(route('admin.store.referral.store'), referralPayload(['store_coupon_id' => $coupon->id]))
        ->assertSessionHasErrors('store_coupon_id');

    expect(StoreReferral::count())->toBe(0);
});

test('creating lands on the code page, where the tracking link is', function () {
    // The link is the whole point of making one, and it is shown nowhere else.
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload())
        ->assertRedirect(route('admin.store.referral.show', StoreReferral::first()->id));
});

test('commands are written with the trigger and flags the module fixes', function () {
    $this->actingAs(User::whereId(1)->first());
    $server = Server::factory()->create();

    $this->post(route('admin.store.referral.store'), referralPayload([
        'is_command_execution_enabled' => true,
        'commands' => [referralCommandPayload(['command' => 'say thanks'], [$server->id])],
    ]))->assertSessionHasNoErrors();

    $command = StoreReferral::first()->commands()->first();

    expect($command->commandable_type)->toBe(StoreReferral::class);
    expect($command->trigger->value)->toBe('purchase');
    expect($command->command)->toBe('say thanks');
    // Order-level, so repeating per unit of a line it is not attached to would mean nothing.
    expect($command->is_repeat_per_quantity)->toBeFalse();
    expect($command->is_run_on_all_servers)->toBeFalse();
    expect($command->servers->pluck('id')->all())->toEqual([$server->id]);
});

test('leaving the server picker empty records run on all servers', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.referral.store'), referralPayload([
        'is_command_execution_enabled' => true,
        'commands' => [referralCommandPayload()],
    ]))->assertSessionHasNoErrors();

    expect(StoreReferral::first()->commands()->first()->is_run_on_all_servers)->toBeTrue();
});

test('editing removes the commands the form no longer sends', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = StoreReferral::factory()->withCommands()->create();
    $keep = StoreCommand::factory()->forOwner($referral)->create(['command' => 'keep me']);
    $drop = StoreCommand::factory()->forOwner($referral)->create(['command' => 'drop me']);

    $this->put(route('admin.store.referral.update', $referral->id), referralPayload([
        'code' => $referral->code,
        'is_command_execution_enabled' => true,
        'commands' => [referralCommandPayload(['id' => $keep->id, 'command' => 'keep me'])],
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_commands', ['id' => $keep->id]);
    $this->assertDatabaseMissing('store_commands', ['id' => $drop->id]);
});

test('saving one referral never touches another owner commands', function () {
    // All three owners share store_commands, so the scoping is the only thing keeping them apart.
    $this->actingAs(User::whereId(1)->first());

    $referral = StoreReferral::factory()->create();
    $otherReferral = StoreReferral::factory()->create();
    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->create();

    $otherCommand = StoreCommand::factory()->forOwner($otherReferral)->create();
    $packageCommand = StoreCommand::factory()->forOwner($package)->create();
    $saleCommand = StoreCommand::factory()->forSale($sale)->create();

    $this->put(route('admin.store.referral.update', $referral->id), referralPayload([
        'code' => $referral->code,
        'is_command_execution_enabled' => true,
        'commands' => [referralCommandPayload()],
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_commands', ['id' => $otherCommand->id]);
    $this->assertDatabaseHas('store_commands', ['id' => $packageCommand->id]);
    $this->assertDatabaseHas('store_commands', ['id' => $saleCommand->id]);
    expect($referral->fresh()->commands()->count())->toBe(1);
});

test('a forged command id belonging to another referral is not stolen', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = StoreReferral::factory()->create();
    $other = StoreReferral::factory()->create();
    $victim = StoreCommand::factory()->forOwner($other)->create(['command' => 'untouched']);

    $this->put(route('admin.store.referral.update', $referral->id), referralPayload([
        'code' => $referral->code,
        'is_command_execution_enabled' => true,
        'commands' => [referralCommandPayload(['id' => $victim->id, 'command' => 'stolen'])],
    ]))->assertSessionHasNoErrors();

    expect($victim->fresh()->command)->toBe('untouched');
    expect($victim->fresh()->commandable_id)->toBe($other->id);
    // Falls through to a create on the referral actually being edited.
    expect($referral->fresh()->commands()->count())->toBe(1);
});

test('the edit page carries the referral and its commands', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = StoreReferral::factory()->create();
    StoreCommand::factory()->forOwner($referral)->create();

    $this->get(route('admin.store.referral.edit', $referral->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreReferral/EditStoreReferral')
            ->where('storeReferral.code', $referral->code)
            ->has('storeReferral.commands', 1)
            ->has('attributionModes')
            ->has('trackingBaseUrl')
        );
});

test('deleting soft deletes, so the money trail survives', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = StoreReferral::factory()->create();

    $this->delete(route('admin.store.referral.delete', $referral->id))
        ->assertRedirect(route('admin.store.referral.index'));

    $this->assertSoftDeleted('store_referrals', ['id' => $referral->id]);
});

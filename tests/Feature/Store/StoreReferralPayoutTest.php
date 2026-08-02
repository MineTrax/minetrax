<?php

use App\Enums\StoreOrderStatus;
use App\Models\Role;
use App\Models\StoreOrder;
use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

/**
 * A referral that has earned $5.00 across two paid orders.
 */
function earnedReferral(): StoreReferral
{
    $referral = StoreReferral::factory()->create(['code' => 'KAKAMORA']);

    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 300,
    ]);
    StoreOrder::factory()->completed()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 200,
    ]);

    return $referral;
}

/**
 * Staff who may manage referrals but not settle up with them.
 *
 * @param  array<int, string>  $permissions
 */
function payoutStaff(array $permissions): User
{
    $role = Role::create([
        'name' => 'payout-staff-'.Str::random(8),
        'display_name' => 'Payout Staff',
        'is_staff' => true,
        'weight' => 1,
    ]);
    $role->givePermissionTo($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('the three figures agree on the listing and the detail page', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();
    StoreReferralPayout::factory()->of(200)->create(['store_referral_id' => $referral->id]);

    $this->get(route('admin.store.referral.index'))
        ->assertInertia(fn ($page) => $page
            ->where('referrals.data.0.earned_base', 500)
            ->where('referrals.data.0.paid_out', 200)
            ->where('referrals.data.0.owed', 300)
        );

    $this->get(route('admin.store.referral.show', $referral->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreReferral/ShowStoreReferral')
            ->where('referral.owed', 300)
            ->has('orders.data', 2)
            ->has('payouts.data', 1)
        );
});

test('recording a payout drops what is owed by exactly that much', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();

    $this->post(route('admin.store.referral.payout', $referral->id), [
        'amount' => 200,
        'reference' => 'PP-12345',
    ])->assertRedirect(route('admin.store.referral.show', $referral->id));

    expect($referral->fresh()->owed())->toBe(300);

    $this->assertDatabaseHas('store_referral_payouts', [
        'store_referral_id' => $referral->id,
        'amount' => 200,
        'reference' => 'PP-12345',
        // Snapshotted, so the history reads correctly if the base currency ever changes.
        'currency' => 'USD',
    ]);
});

test('paying more than is outstanding is refused, and names the figure', function () {
    // Nearly always a typo, and the alternative is a negative balance nobody asked for.
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();

    $this->post(route('admin.store.referral.payout', $referral->id), ['amount' => 501])
        ->assertSessionHasErrors(['amount']);

    expect($referral->fresh()->paidOut())->toBe(0);

    // Exactly the outstanding amount is fine.
    $this->post(route('admin.store.referral.payout', $referral->id), ['amount' => 500])
        ->assertSessionHasNoErrors();
});

test('deleting a payout puts the amount straight back', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();
    $payout = StoreReferralPayout::factory()->of(200)->create(['store_referral_id' => $referral->id]);

    expect($referral->fresh()->owed())->toBe(300);

    $this->delete(route('admin.store.referral.payout.delete', [$referral->id, $payout->id]))
        ->assertRedirect(route('admin.store.referral.show', $referral->id));

    expect($referral->fresh()->owed())->toBe(500);
});

test('a payout belonging to another referral is not deletable through this one', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();
    $other = StoreReferral::factory()->create();
    $payout = StoreReferralPayout::factory()->of(100)->create(['store_referral_id' => $other->id]);

    $this->delete(route('admin.store.referral.payout.delete', [$referral->id, $payout->id]))
        ->assertStatus(404);

    $this->assertDatabaseHas('store_referral_payouts', ['id' => $payout->id]);
});

test('recording and deleting both need the payout permission', function () {
    $staff = payoutStaff(['read store_referrals', 'update store_referrals']);
    $referral = earnedReferral();
    $payout = StoreReferralPayout::factory()->of(100)->create(['store_referral_id' => $referral->id]);

    $this->actingAs($staff)
        ->post(route('admin.store.referral.payout', $referral->id), ['amount' => 100])
        ->assertStatus(403);

    $this->actingAs($staff)
        ->delete(route('admin.store.referral.payout.delete', [$referral->id, $payout->id]))
        ->assertStatus(403);

    // The page itself is readable, it just does not offer the form.
    $this->actingAs($staff)
        ->get(route('admin.store.referral.show', $referral->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->where('canPayout', false));
});

test('granting the permission opens the form', function () {
    $staff = payoutStaff(['read store_referrals', 'payout store_referrals']);
    $referral = earnedReferral();

    $this->actingAs($staff)
        ->get(route('admin.store.referral.show', $referral->id))
        ->assertInertia(fn ($page) => $page->where('canPayout', true));

    $this->actingAs($staff)
        ->post(route('admin.store.referral.payout', $referral->id), ['amount' => 100])
        ->assertSessionHasNoErrors();

    expect($referral->fresh()->owed())->toBe(400);
});

test('cancelled and refunded orders contribute nothing to what is owed', function () {
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();

    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::CANCELLED,
        'referral_earning_base' => 999,
    ]);
    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::REFUNDED,
        'referral_earning_base' => 999,
    ]);

    expect($referral->fresh()->owed())->toBe(500);

    // They stay in the list contributing zero, so it is visible why the total is what it is.
    $this->get(route('admin.store.referral.show', $referral->id))
        ->assertInertia(function ($page) {
            $rows = collect($page->toArray()['props']['orders']['data']);

            expect($rows)->toHaveCount(4);
            expect($rows->where('counts_towards_balance', false))->toHaveCount(2);
        });
});

test('a refund after a payout leaves a negative balance rather than a clamped zero', function () {
    // Clamping would quietly forgive an overpayment the owner needs to see.
    $this->actingAs(User::whereId(1)->first());
    $referral = earnedReferral();

    $this->post(route('admin.store.referral.payout', $referral->id), ['amount' => 500])
        ->assertSessionHasNoErrors();

    expect($referral->fresh()->owed())->toBe(0);

    $referral->orders()->update(['status' => StoreOrderStatus::REFUNDED]);

    expect($referral->fresh()->owed())->toBe(-500);

    $this->get(route('admin.store.referral.index'))
        ->assertInertia(fn ($page) => $page->where('referrals.data.0.owed', -500));
});

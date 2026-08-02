<?php

use App\Models\Role;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StoreReferral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

/**
 * Staff whose visibility is limited to what they created.
 *
 * Given a role of their own rather than `admin`, which already grants the global coupon
 * permissions — attaching that role and then calling syncPermissions on the user would not take
 * them away, and every one of these assertions would pass for the wrong reason.
 *
 * Not a superadmin either: Gate::before waves those through every check.
 *
 * @param  array<int, string>  $permissions
 */
function scopedStaff(array $permissions): User
{
    $role = Role::create([
        'name' => 'scoped-staff-'.Str::random(8),
        'display_name' => 'Scoped Staff',
        'is_staff' => true,
        'weight' => 1,
    ]);
    $role->givePermissionTo($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

// -- Coupons -----------------------------------------------------------------------------------

test('read_own shows a coupon writer only their own codes', function () {
    $mine = scopedStaff(['read_own store_coupons']);
    $theirs = User::factory()->create();

    $ownCoupon = StoreCoupon::factory()->create(['code' => 'MINE', 'created_by' => $mine->id]);
    StoreCoupon::factory()->create(['code' => 'THEIRS', 'created_by' => $theirs->id]);
    // Seeded, imported, or written before the column existed. Nobody's own.
    StoreCoupon::factory()->create(['code' => 'ORPHAN', 'created_by' => null]);

    $this->actingAs($mine)
        ->get(route('admin.store.coupon.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) use ($ownCoupon) {
            $rows = collect($page->toArray()['props']['coupons']['data']);

            expect($rows)->toHaveCount(1);
            expect($rows->first()['id'])->toBe($ownCoupon->id);
        });
});

test('the bare read permission still means every coupon', function () {
    // The upgrade contract: no existing role loses anything.
    $staff = scopedStaff(['read store_coupons']);

    StoreCoupon::factory()->create(['created_by' => User::factory()->create()->id]);
    StoreCoupon::factory()->create(['created_by' => null]);
    StoreCoupon::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)
        ->get(route('admin.store.coupon.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->count('coupons.data', 3));
});

test('the coupon listing reports edit rights per row not per page', function () {
    // A page can hold rows this user may edit beside rows they may not.
    $staff = scopedStaff(['read store_coupons', 'update_own store_coupons', 'delete_own store_coupons']);

    $own = StoreCoupon::factory()->create(['created_by' => $staff->id]);
    $other = StoreCoupon::factory()->create(['created_by' => User::factory()->create()->id]);

    $this->actingAs($staff)
        ->get(route('admin.store.coupon.index'))
        ->assertInertia(function ($page) use ($own, $other) {
            $rows = collect($page->toArray()['props']['coupons']['data'])->keyBy('id');

            expect($rows[$own->id]['can_update'])->toBeTrue();
            expect($rows[$own->id]['can_delete'])->toBeTrue();
            expect($rows[$other->id]['can_update'])->toBeFalse();
            expect($rows[$other->id]['can_delete'])->toBeFalse();
        });
});

test('update_own refuses somebody elses coupon by direct url', function () {
    // The whole point of scoping the actions as well as the listing: a hidden row that is still
    // editable by URL is a filtered view, not access control.
    $staff = scopedStaff(['read_own store_coupons', 'update_own store_coupons']);
    $other = StoreCoupon::factory()->create(['created_by' => User::factory()->create()->id]);
    $own = StoreCoupon::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)->get(route('admin.store.coupon.edit', $other->id))->assertStatus(403);
    $this->actingAs($staff)->get(route('admin.store.coupon.edit', $own->id))->assertStatus(200);
});

test('delete_own refuses somebody elses coupon and allows your own', function () {
    $staff = scopedStaff(['read_own store_coupons', 'delete_own store_coupons']);
    $other = StoreCoupon::factory()->create(['created_by' => User::factory()->create()->id]);
    $own = StoreCoupon::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)->delete(route('admin.store.coupon.delete', $other->id))->assertStatus(403);
    $this->assertDatabaseHas('store_coupons', ['id' => $other->id]);

    $this->actingAs($staff)->delete(route('admin.store.coupon.delete', $own->id));
    $this->assertDatabaseMissing('store_coupons', ['id' => $own->id]);
});

test('an ownerless coupon belongs to nobody so read_own never reveals it', function () {
    // "Mine" must not quietly widen to every row with a null column.
    $staff = scopedStaff(['read_own store_coupons', 'update_own store_coupons']);
    $orphan = StoreCoupon::factory()->create(['created_by' => null]);

    $this->actingAs($staff)->get(route('admin.store.coupon.edit', $orphan->id))->assertStatus(403);
});

test('holding neither coupon read permission is still a refusal', function () {
    $staff = scopedStaff(['read store_packages']);

    $this->actingAs($staff)->get(route('admin.store.coupon.index'))->assertStatus(403);
});

// -- Gift cards --------------------------------------------------------------------------------

test('read_own hides every purchased gift card because none of them has a creator', function () {
    $staff = scopedStaff(['read_own store_gift_cards']);

    $issuedByThem = StoreGiftCard::factory()->create(['created_by' => $staff->id]);
    StoreGiftCard::factory()->create(['created_by' => User::factory()->create()->id]);
    // How a bought card looks: minted by the fulfilment job, no staff behind it.
    StoreGiftCard::factory()->create(['created_by' => null]);

    $this->actingAs($staff)
        ->get(route('admin.store.gift-card.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) use ($issuedByThem) {
            $rows = collect($page->toArray()['props']['cards']['data']);

            expect($rows)->toHaveCount(1);
            expect($rows->first()['id'])->toBe($issuedByThem->id);
        });
});

test('the bare read permission still means every gift card', function () {
    $staff = scopedStaff(['read store_gift_cards']);

    StoreGiftCard::factory()->create(['created_by' => null]);
    StoreGiftCard::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)
        ->get(route('admin.store.gift-card.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->count('cards.data', 2));
});

test('read_own refuses another staff members card page', function () {
    $staff = scopedStaff(['read_own store_gift_cards']);
    $other = StoreGiftCard::factory()->create(['created_by' => User::factory()->create()->id]);
    $own = StoreGiftCard::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)->get(route('admin.store.gift-card.show', $other->id))->assertStatus(403);
    $this->actingAs($staff)->get(route('admin.store.gift-card.show', $own->id))->assertStatus(200);
});

test('update_own cannot adjust the balance of a card it did not issue', function () {
    // Adjusting is an update: it hands out spendable credit, so it is scoped the same way.
    $staff = scopedStaff(['read store_gift_cards', 'update_own store_gift_cards']);
    $other = StoreGiftCard::factory()->create(['balance' => 1000, 'created_by' => User::factory()->create()->id]);

    $this->actingAs($staff)
        ->post(route('admin.store.gift-card.adjust', $other->id), ['amount' => 500, 'note' => 'nope'])
        ->assertStatus(403);

    expect((int) $other->fresh()->balance)->toBe(1000);
});

test('the card page reports rights against that card not the bare permission', function () {
    $staff = scopedStaff(['read store_gift_cards', 'update_own store_gift_cards']);
    $other = StoreGiftCard::factory()->create(['created_by' => User::factory()->create()->id]);

    $this->actingAs($staff)
        ->get(route('admin.store.gift-card.show', $other->id))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->where('cardPermissions.update', false));
});

// -- Referrals ---------------------------------------------------------------------------------

test('read_own shows a referral writer only their own codes', function () {
    $mine = scopedStaff(['read_own store_referrals']);
    $theirs = User::factory()->create();

    StoreReferral::factory()->create(['code' => 'MINE', 'created_by' => $mine->id]);
    StoreReferral::factory()->create(['code' => 'THEIRS', 'created_by' => $theirs->id]);
    // Seeded, imported, or written before the column existed. Nobody's own.
    StoreReferral::factory()->create(['code' => 'ORPHAN', 'created_by' => null]);

    $this->actingAs($mine)
        ->get(route('admin.store.referral.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) {
            $codes = collect($page->toArray()['props']['referrals']['data'])->pluck('code');

            expect($codes->all())->toEqual(['MINE']);
        });
});

test('update_own refuses somebody elses referral by direct url', function () {
    // A hidden row that is still editable by URL is a filtered view, not access control.
    $staff = scopedStaff(['read_own store_referrals', 'update_own store_referrals']);
    $other = StoreReferral::factory()->create(['created_by' => User::factory()->create()->id]);
    $own = StoreReferral::factory()->create(['created_by' => $staff->id]);

    $this->actingAs($staff)->get(route('admin.store.referral.edit', $other->id))->assertStatus(403);
    $this->actingAs($staff)->get(route('admin.store.referral.edit', $own->id))->assertStatus(200);
});

test('recording a payout needs its own permission, whoever wrote the code', function () {
    // Managing the promotion and settling up with the referrer are different jobs, so creating the
    // code grants nothing towards paying for it.
    $staff = scopedStaff(['read store_referrals', 'update store_referrals', 'create store_referrals']);
    $referral = StoreReferral::factory()->create(['created_by' => $staff->id]);
    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 500,
    ]);

    $this->actingAs($staff)
        ->post(route('admin.store.referral.payout', $referral->id), ['amount' => 100])
        ->assertStatus(403);

    expect($referral->fresh()->paidOut())->toBe(0);
});

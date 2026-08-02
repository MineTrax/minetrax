<?php

use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

test('all store policies are registered', function () {
    $models = [
        StoreCategory::class, StorePackage::class, StoreCurrency::class,
        StoreOrder::class, StoreCoupon::class, StoreSale::class, StoreBan::class,
        StoreReferral::class,
    ];

    foreach ($models as $model) {
        expect(Gate::getPolicyFor($model))->not->toBeNull("No policy registered for [{$model}].");
    }
});

test('a user without permission is denied', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', StorePackage::class))->toBeFalse();
    expect($user->can('create', StorePackage::class))->toBeFalse();
    expect($user->can('viewAny', StoreOrder::class))->toBeFalse();
});

test('a user with the permission is allowed', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['read store_packages', 'create store_packages']);

    expect($user->can('viewAny', StorePackage::class))->toBeTrue();
    expect($user->can('create', StorePackage::class))->toBeTrue();
    expect($user->can('delete', StorePackage::factory()->create()))->toBeFalse();
});

test('disabling the module denies everyone except superadmin', function () {
    config(['store.enabled' => false]);

    $user = User::factory()->create();
    $user->givePermissionTo(['read store_packages', 'create store_packages']);

    expect($user->can('viewAny', StorePackage::class))->toBeFalse('The before() gate must deny when the module is off.');
    expect($user->can('create', StorePackage::class))->toBeFalse();

    // Gate::before for superadmin runs ahead of the policy, matching BanWarden's behaviour.
    // Routes and nav are hidden anyway when the module is disabled.
    expect(User::whereId(1)->first()->can('viewAny', StorePackage::class))->toBeTrue();
});

test('refund and resend are separate abilities from plain update', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['read store_orders', 'update store_orders']);
    $order = StoreOrder::factory()->create();

    expect($user->can('update', $order))->toBeTrue();
    expect($user->can('refund', $order))->toBeFalse('Refunding money must not come free with update.');
    expect($user->can('resend', $order))->toBeFalse();

    $user->givePermissionTo(['refund store_orders', 'resend store_orders']);
    $user->forgetCachedPermissions();

    expect($user->fresh()->can('refund', $order))->toBeTrue();
    expect($user->fresh()->can('resend', $order))->toBeTrue();
});

test('a buyer can view their own order without any permission', function () {
    $buyer = User::factory()->create();
    $ownOrder = StoreOrder::factory()->forUser($buyer)->create();
    $otherOrder = StoreOrder::factory()->create();

    expect($buyer->can('view', $ownOrder))->toBeTrue();
    expect($buyer->can('view', $otherOrder))->toBeFalse('A buyer must not read another buyer order.');
});

test('a guest order is not viewable by an arbitrary user', function () {
    $user = User::factory()->create();
    $guestOrder = StoreOrder::factory()->guest()->create();

    expect($user->can('view', $guestOrder))->toBeFalse();
});

test('paying a referrer is a separate ability from managing the code', function () {
    // Setting up a creator code is promotions work; settling with them moves money out of the
    // business. Someone trusted with the first is not automatically trusted with the second.
    $user = User::factory()->create();
    $user->givePermissionTo(['read store_referrals', 'update store_referrals', 'create store_referrals']);
    $referral = StoreReferral::factory()->create();

    expect($user->can('update', $referral))->toBeTrue();
    expect($user->can('payout', StoreReferral::class))->toBeFalse();

    $user->givePermissionTo('payout store_referrals');
    $user->forgetCachedPermissions();

    expect($user->fresh()->can('payout', StoreReferral::class))->toBeTrue();
});

test('a referrer sees their own figures and nobody else does', function () {
    // Owning the code is the whole authorisation here — no staff permission is involved, and
    // holding one does not open somebody else's earnings.
    $referrer = User::factory()->create();
    $stranger = User::factory()->create();
    $staff = User::factory()->create();
    $staff->givePermissionTo('read store_referrals');

    $referral = StoreReferral::factory()->forUser($referrer)->create();
    $unclaimed = StoreReferral::factory()->create();

    expect($referrer->can('viewDashboard', $referral))->toBeTrue();
    expect($stranger->can('viewDashboard', $referral))->toBeFalse();
    expect($staff->can('viewDashboard', $referral))->toBeFalse('Reading every code is not the same as being one.');

    // A code with no member attached belongs to nobody, so it is nobody's dashboard.
    expect($referrer->can('viewDashboard', $unclaimed))->toBeFalse();
});

test('the module toggle closes the referrer dashboard too', function () {
    config(['store.enabled' => false]);

    $referrer = User::factory()->create();
    $referral = StoreReferral::factory()->forUser($referrer)->create();

    expect($referrer->can('viewDashboard', $referral))->toBeFalse();
});

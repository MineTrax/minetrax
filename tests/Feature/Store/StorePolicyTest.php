<?php

use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
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

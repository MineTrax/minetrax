<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store config file is registered with expected defaults', function () {
    expect(config('store'))->toBeArray();

    // STORE_ENABLED is pinned false in phpunit.xml, so this asserts the module stays off
    // unless something opts in — not that the developer's own .env happens to have it off.
    expect(config('store.enabled'))->toBeFalse('The store must be off unless explicitly enabled.');
    expect(config('store.gateways'))->toBeArray();
    expect(config('store.command_max_attempts'))->toEqual(3);
    expect(config('store.deferred_attention_days'))->toEqual(3);
    expect(config('store.cart_max_items'))->toEqual(20);
});

test('command max attempts allows the sweeper to actually retry', function () {
    // The every-minute sweeper only retries rows where attempts < max_attempts, so a value of
    // 1 (as every pre-existing caller uses) means store deliveries would never be retried.
    expect(config('store.command_max_attempts'))->toBeGreaterThan(1);
});

test('store settings resolve with defaults', function () {
    $settings = app(StoreSettings::class);

    expect($settings->base_currency)->toEqual('USD');
    expect($settings->currency_rate_source)->toEqual('manual');
    expect($settings->tax_mode)->toEqual('none');
    expect($settings->tax_rate_bp)->toEqual(0);
    expect($settings->enable_guest_checkout)->toBeTrue();
    expect($settings->mojang_username_verification)->toBeTrue();
    expect($settings->enabled_gateways)->toEqual(['manual']);
    expect($settings->gateway_credentials)->toEqual([]);
});

test('store settings persist changes', function () {
    $settings = app(StoreSettings::class);
    $settings->base_currency = 'EUR';
    $settings->tax_mode = 'exclusive';
    $settings->tax_rate_bp = 2000;
    $settings->save();

    $fresh = app(StoreSettings::class);

    expect($fresh->base_currency)->toEqual('EUR');
    expect($fresh->tax_mode)->toEqual('exclusive');
    expect($fresh->tax_rate_bp)->toEqual(2000);
});

test('gateway credentials are stored encrypted', function () {
    $settings = app(StoreSettings::class);
    $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_supersecret']];
    $settings->save();

    expect(app(StoreSettings::class)->gateway_credentials['stripe']['secret_key'])->toEqual('sk_test_supersecret');

    $raw = DB::table('settings')->where('group', 'store')->where('name', 'gateway_credentials')->value('payload');
    $this->assertStringNotContainsString('sk_test_supersecret', $raw, 'Gateway credentials must not be readable in the settings table.');
});

test('store permissions are seeded', function () {
    $expected = [
        'create store_categories', 'read store_categories', 'update store_categories', 'delete store_categories',
        'create store_packages', 'read store_packages', 'update store_packages', 'delete store_packages',
        'create store_currencies', 'read store_currencies', 'update store_currencies', 'delete store_currencies',
        'read store_orders', 'update store_orders', 'delete store_orders', 'refund store_orders', 'resend store_orders',
        'read store_payments',
        'create store_coupons', 'read store_coupons', 'update store_coupons', 'delete store_coupons',
        'create store_sales', 'read store_sales', 'update store_sales', 'delete store_sales',
        'create store_gift_cards', 'read store_gift_cards', 'update store_gift_cards', 'delete store_gift_cards',
        'create store_bans', 'read store_bans', 'update store_bans', 'delete store_bans',
        'view store_statistics',
    ];

    foreach ($expected as $permission) {
        expect(Permission::where('name', $permission)->first())->not->toBeNull("Missing permission [{$permission}].");
    }
});

test('store permissions are prefixed so sidebar wildcard matching works', function () {
    // The admin sidebar gates on canWild("store_"), a substring match over permission names.
    $storePermissions = Permission::where('name', 'like', '%store_%')->pluck('name');

    expect($storePermissions->count())->toBeGreaterThanOrEqual(35);
});

test('admin role receives a curated store permission subset', function () {
    $admin = Role::where('name', 'admin')->first();

    expect($admin->hasPermissionTo('read store_orders'))->toBeTrue();
    expect($admin->hasPermissionTo('refund store_orders'))->toBeTrue();
    expect($admin->hasPermissionTo('create store_packages'))->toBeTrue();

    // Gift card issuance and currency deletion stay superadmin-only.
    expect($admin->hasPermissionTo('create store_gift_cards'))->toBeFalse();
    expect($admin->hasPermissionTo('delete store_currencies'))->toBeFalse();
});

test('superadmin has every store permission', function () {
    $superAdmin = Role::where('name', Role::SUPER_ADMIN_ROLE_NAME)->first();

    expect($superAdmin->hasPermissionTo('create store_gift_cards'))->toBeTrue();
    expect($superAdmin->hasPermissionTo('delete store_currencies'))->toBeTrue();
});

test('shared prop reports the store as disabled by default', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('store.enabled', false));
});

test('shared prop exposes store details when enabled', function () {
    config(['store.enabled' => true]);
    $this->actingAs(User::whereId(1)->first());

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('store.enabled', true)
            ->where('store.baseCurrency', 'USD')
            ->has('store.name')
        );
});

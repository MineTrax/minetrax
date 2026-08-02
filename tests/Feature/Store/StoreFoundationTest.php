<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\StorePaymentGateway;
use App\Models\User;
use App\Settings\StoreSettings;
use Database\Seeders\StorePaymentGatewaySeeder;
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
    expect($settings->enable_guest_checkout)->toBeTrue();
    expect($settings->mojang_username_verification)->toBeTrue();
});

test('every configured gateway is seeded a row', function () {
    // Driven by config, so adding a driver needs no edit to the seeder.
    foreach (array_keys(config('store.gateways')) as $key) {
        expect(StorePaymentGateway::where('key', $key)->exists())->toBeTrue("Missing a row for {$key}.");
    }
});

test('only the manual gateway is on out of the box', function () {
    // Everything else needs credentials before it can charge anything, so a fresh install has one
    // working checkout rather than three broken ones.
    expect(StorePaymentGateway::enabled()->pluck('key')->all())->toEqual(['manual']);
});

test('seeding again leaves a configured gateway exactly as it was', function () {
    // The point of firstOrCreate: adding a fourth gateway later must not switch the other three
    // off or forget their keys.
    $stripe = StorePaymentGateway::firstWhere('key', 'stripe');
    $stripe->update(['is_enabled' => true, 'credentials' => ['secret_key' => 'sk_test_keepme']]);

    $this->seed(StorePaymentGatewaySeeder::class);

    $fresh = StorePaymentGateway::firstWhere('key', 'stripe');
    expect($fresh->is_enabled)->toBeTrue();
    expect($fresh->credential('secret_key'))->toEqual('sk_test_keepme');
    expect(StorePaymentGateway::where('key', 'stripe')->count())->toBe(1);
});

test('store settings persist changes', function () {
    $settings = app(StoreSettings::class);
    $settings->base_currency = 'EUR';
    $settings->save();

    $fresh = app(StoreSettings::class);

    expect($fresh->base_currency)->toEqual('EUR');
});

test('gateway credentials are stored encrypted', function () {
    $stripe = StorePaymentGateway::firstWhere('key', 'stripe');
    $stripe->update(['credentials' => ['secret_key' => 'sk_test_supersecret']]);

    expect(StorePaymentGateway::firstWhere('key', 'stripe')->credential('secret_key'))
        ->toEqual('sk_test_supersecret');

    $raw = DB::table('store_payment_gateways')->where('key', 'stripe')->value('credentials');
    $this->assertStringNotContainsString('sk_test_supersecret', $raw, 'Gateway credentials must not be readable in the database.');
});

test('credentials never ride along in a serialised gateway', function () {
    // The model is hidden by default so a stray toArray() cannot leak keys into a response.
    $stripe = StorePaymentGateway::firstWhere('key', 'stripe');
    $stripe->update(['credentials' => ['secret_key' => 'sk_test_supersecret']]);

    expect($stripe->fresh()->toArray())->not->toHaveKey('credentials');
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
        'create store_referrals', 'read store_referrals', 'update store_referrals', 'delete store_referrals',
        'payout store_referrals',
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

    // Setting up creator codes is promotions work and comes with the rest.
    expect($admin->hasPermissionTo('create store_referrals'))->toBeTrue();

    // Gift card issuance and currency deletion stay superadmin-only.
    expect($admin->hasPermissionTo('create store_gift_cards'))->toBeFalse();
    expect($admin->hasPermissionTo('delete store_currencies'))->toBeFalse();

    // So does paying a referrer: that books money leaving the business, and it is a separate job
    // from running the promotion.
    expect($admin->hasPermissionTo('payout store_referrals'))->toBeFalse();
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

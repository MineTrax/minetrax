<?php

namespace Tests\Feature\Store;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the Store module foundation: its own config file, settings group, permissions, and the
 * shared Inertia prop that gates the frontend.
 */
class StoreFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_config_file_is_registered_with_expected_defaults()
    {
        $this->assertIsArray(config('store'));
        // STORE_ENABLED is pinned false in phpunit.xml, so this asserts the module stays off
        // unless something opts in — not that the developer's own .env happens to have it off.
        $this->assertFalse(config('store.enabled'), 'The store must be off unless explicitly enabled.');
        $this->assertIsArray(config('store.gateways'));
        $this->assertEquals(3, config('store.command_max_attempts'));
        $this->assertEquals(3, config('store.deferred_attention_days'));
        $this->assertEquals(20, config('store.cart_max_items'));
    }

    public function test_command_max_attempts_allows_the_sweeper_to_actually_retry()
    {
        // The every-minute sweeper only retries rows where attempts < max_attempts, so a value of
        // 1 (as every pre-existing caller uses) means store deliveries would never be retried.
        $this->assertGreaterThan(1, config('store.command_max_attempts'));
    }

    public function test_store_settings_resolve_with_defaults()
    {
        $settings = app(StoreSettings::class);

        $this->assertEquals('USD', $settings->base_currency);
        $this->assertEquals('manual', $settings->currency_rate_source);
        $this->assertEquals('none', $settings->tax_mode);
        $this->assertEquals(0, $settings->tax_rate_bp);
        $this->assertTrue($settings->enable_guest_checkout);
        $this->assertTrue($settings->mojang_username_verification);
        $this->assertEquals(['manual'], $settings->enabled_gateways);
        $this->assertEquals([], $settings->gateway_credentials);
    }

    public function test_store_settings_persist_changes()
    {
        $settings = app(StoreSettings::class);
        $settings->base_currency = 'EUR';
        $settings->tax_mode = 'exclusive';
        $settings->tax_rate_bp = 2000;
        $settings->save();

        $fresh = app(StoreSettings::class);

        $this->assertEquals('EUR', $fresh->base_currency);
        $this->assertEquals('exclusive', $fresh->tax_mode);
        $this->assertEquals(2000, $fresh->tax_rate_bp);
    }

    public function test_gateway_credentials_are_stored_encrypted()
    {
        $settings = app(StoreSettings::class);
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_supersecret']];
        $settings->save();

        $this->assertEquals(
            'sk_test_supersecret',
            app(StoreSettings::class)->gateway_credentials['stripe']['secret_key']
        );

        $raw = \DB::table('settings')->where('group', 'store')->where('name', 'gateway_credentials')->value('payload');
        $this->assertStringNotContainsString('sk_test_supersecret', $raw, 'Gateway credentials must not be readable in the settings table.');
    }

    public function test_store_permissions_are_seeded()
    {
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
            $this->assertNotNull(
                Permission::where('name', $permission)->first(),
                "Missing permission [{$permission}]."
            );
        }
    }

    public function test_store_permissions_are_prefixed_so_sidebar_wildcard_matching_works()
    {
        // The admin sidebar gates on canWild("store_"), a substring match over permission names.
        $storePermissions = Permission::where('name', 'like', '%store_%')->pluck('name');

        $this->assertGreaterThanOrEqual(35, $storePermissions->count());
    }

    public function test_admin_role_receives_a_curated_store_permission_subset()
    {
        $admin = Role::where('name', 'admin')->first();

        $this->assertTrue($admin->hasPermissionTo('read store_orders'));
        $this->assertTrue($admin->hasPermissionTo('refund store_orders'));
        $this->assertTrue($admin->hasPermissionTo('create store_packages'));

        // Gift card issuance and currency deletion stay superadmin-only.
        $this->assertFalse($admin->hasPermissionTo('create store_gift_cards'));
        $this->assertFalse($admin->hasPermissionTo('delete store_currencies'));
    }

    public function test_superadmin_has_every_store_permission()
    {
        $superAdmin = Role::where('name', Role::SUPER_ADMIN_ROLE_NAME)->first();

        $this->assertTrue($superAdmin->hasPermissionTo('create store_gift_cards'));
        $this->assertTrue($superAdmin->hasPermissionTo('delete store_currencies'));
    }

    public function test_shared_prop_reports_the_store_as_disabled_by_default()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->get(route('home'))
            ->assertInertia(fn ($page) => $page->where('store.enabled', false));
    }

    public function test_shared_prop_exposes_store_details_when_enabled()
    {
        config(['store.enabled' => true]);
        $this->actingAs(User::whereId(1)->first());

        $this->get(route('home'))
            ->assertInertia(fn ($page) => $page
                ->where('store.enabled', true)
                ->where('store.baseCurrency', 'USD')
                ->has('store.name')
            );
    }
}

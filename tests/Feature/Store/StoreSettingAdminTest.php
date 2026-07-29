<?php

namespace Tests\Feature\Store;

use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Storefront, currency, tax and checkout settings. Payment gateways have their own screen and
 * their own test — see StorePaymentGatewayAdminTest.
 */
class StoreSettingAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        $this->superadmin = User::whereId(1)->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'store_name' => 'My Store',
            'store_description' => null,
            'base_currency' => 'USD',
            'currency_rate_source' => 'manual',
            'tax_mode' => 'none',
            'tax_rate_bp' => 0,
            'tax_label' => 'Tax',
            'enable_guest_checkout' => true,
            'require_email_on_guest_checkout' => true,
            'mojang_username_verification' => true,
            'terms_text' => null,
            'show_recent_purchases' => true,
            'hide_buyer_identity' => false,
            'notify_staff_on_purchase' => true,
            'auto_ban_on_chargeback' => false,
        ], $overrides);
    }

    // --- Access ------------------------------------------------------------------------------

    public function test_a_guest_is_redirected_to_login()
    {
        $this->get(route('admin.setting.store.show'))->assertRedirect(route('login'));
    }

    public function test_a_user_without_the_settings_permission_is_forbidden()
    {
        // 403 rather than the admin group's usual redirect, matching every other settings page.
        $this->actingAs(User::factory()->create())
            ->get(route('admin.setting.store.show'))
            ->assertForbidden();
    }

    public function test_a_superadmin_sees_the_page()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.setting.store.show'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Setting/StoreSetting')
                ->has('settings')
                ->has('currencies')
            );
    }

    /**
     * Credentials belong to the payment gateway screen. Nothing about them should be within reach
     * of a form whose job is toggling guest checkout.
     */
    public function test_the_settings_page_carries_no_gateway_data_at_all()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual', 'stripe'];
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_supersecret']];
        $settings->save();

        $response = $this->actingAs($this->superadmin)->get(route('admin.setting.store.show'));

        $response->assertOk();
        $response->assertDontSee('sk_test_supersecret');

        $response->assertInertia(fn ($page) => $page
            ->missing('settings.gateway_credentials')
            ->missing('settings.enabled_gateways')
            ->missing('gateways')
        );
    }

    public function test_saving_the_settings_leaves_the_gateway_configuration_untouched()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual', 'stripe'];
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_keepme']];
        $settings->save();

        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['store_name' => 'Renamed']))
            ->assertSessionHasNoErrors();

        $fresh = app(StoreSettings::class)->refresh();

        $this->assertEquals('Renamed', $fresh->store_name);
        $this->assertEquals(['manual', 'stripe'], $fresh->enabled_gateways);
        $this->assertEquals('sk_test_keepme', $fresh->gateway_credentials['stripe']['secret_key']);
    }

    // --- Ordinary settings ---------------------------------------------------------------------

    public function test_the_settings_are_saved()
    {
        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'store_name' => 'Diamond Shop',
            'tax_mode' => 'exclusive',
            'tax_rate_bp' => 2000,
            'enable_guest_checkout' => false,
            'mojang_username_verification' => false,
        ]))->assertRedirect();

        $settings = app(StoreSettings::class)->refresh();

        $this->assertEquals('Diamond Shop', $settings->store_name);
        $this->assertEquals('exclusive', $settings->tax_mode);
        $this->assertEquals(2000, $settings->tax_rate_bp);
        $this->assertFalse($settings->enable_guest_checkout);
        $this->assertFalse($settings->mojang_username_verification);
    }

    public function test_a_tax_rate_above_one_hundred_percent_is_rejected()
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['tax_rate_bp' => 10001]))
            ->assertSessionHasErrors('tax_rate_bp');
    }

    public function test_an_invalid_tax_mode_is_rejected()
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['tax_mode' => 'sometimes']))
            ->assertSessionHasErrors('tax_mode');
    }

    /**
     * Historical orders recorded base_total against the base currency in force at the time, so
     * swapping it later would silently rewrite revenue history.
     */
    public function test_the_base_currency_is_locked_once_orders_exist()
    {
        StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => false]);
        StoreOrder::factory()->create();

        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['base_currency' => 'EUR']))
            ->assertSessionHasErrors('base_currency');

        $this->assertEquals('USD', app(StoreSettings::class)->refresh()->base_currency);
    }

    public function test_the_base_currency_can_be_changed_before_the_first_order()
    {
        StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => false]);

        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['base_currency' => 'EUR']))
            ->assertSessionHasNoErrors();

        $this->assertEquals('EUR', app(StoreSettings::class)->refresh()->base_currency);
    }
}

<?php

namespace Tests\Feature\Store;

use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSettingAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        StoreCurrency::factory()->base()->create();

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
            'enabled_gateways' => ['manual'],
            'gateway_credentials' => [],
            'show_recent_purchases' => true,
            'hide_buyer_identity' => false,
            'notify_staff_on_purchase' => true,
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
                ->has('gateways')
                ->has('currencies')
            );
    }

    // --- Secrets never round-trip ---------------------------------------------------------------

    public function test_the_raw_credential_bag_is_never_sent_to_the_browser()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual', 'stripe'];
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_supersecret', 'webhook_secret' => 'whsec_supersecret']];
        $settings->save();

        $response = $this->actingAs($this->superadmin)->get(route('admin.setting.store.show'));

        $response->assertOk();
        $response->assertDontSee('sk_test_supersecret');
        $response->assertDontSee('whsec_supersecret');

        $response->assertInertia(function ($page) {
            $page->missing('settings.gateway_credentials');

            $stripe = collect($page->toArray()['props']['gateways'])->firstWhere('key', 'stripe');
            $this->assertEquals('********', $stripe['credentials']['secret_key']);
            $this->assertEquals('********', $stripe['credentials']['webhook_secret']);
        });
    }

    public function test_an_unset_secret_is_sent_as_null_rather_than_a_mask()
    {
        // A masked empty field would read as "already configured" and be impossible to fill in.
        $this->actingAs($this->superadmin)
            ->get(route('admin.setting.store.show'))
            ->assertInertia(function ($page) {
                $stripe = collect($page->toArray()['props']['gateways'])->firstWhere('key', 'stripe');
                $this->assertNull($stripe['credentials']['secret_key']);
            });
    }

    public function test_a_secret_submitted_unchanged_as_the_mask_is_kept()
    {
        $settings = app(StoreSettings::class);
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_original', 'webhook_secret' => 'whsec_original']];
        $settings->save();

        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'enabled_gateways' => ['manual', 'stripe'],
            'gateway_credentials' => [
                'stripe' => ['secret_key' => '********', 'webhook_secret' => 'whsec_rotated'],
            ],
        ]))->assertRedirect();

        $stored = app(StoreSettings::class)->refresh()->gateway_credentials;

        $this->assertEquals('sk_test_original', $stored['stripe']['secret_key'], 'An untouched secret must survive the round trip.');
        $this->assertEquals('whsec_rotated', $stored['stripe']['webhook_secret'], 'A changed secret must be written.');
    }

    public function test_only_fields_a_driver_declares_are_stored()
    {
        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'enabled_gateways' => ['stripe'],
            'gateway_credentials' => [
                'stripe' => ['secret_key' => 'sk_test_1', 'webhook_secret' => 'whsec_1', 'evil' => 'payload'],
            ],
        ]))->assertRedirect();

        $stored = app(StoreSettings::class)->refresh()->gateway_credentials;

        $this->assertArrayNotHasKey('evil', $stored['stripe']);
        $this->assertEquals('sk_test_1', $stored['stripe']['secret_key']);
    }

    public function test_a_credential_for_an_unregistered_gateway_is_discarded()
    {
        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'gateway_credentials' => ['notagateway' => ['token' => 'x']],
        ]))->assertRedirect();

        $this->assertArrayNotHasKey('notagateway', app(StoreSettings::class)->refresh()->gateway_credentials);
    }

    // --- Enabling gateways ------------------------------------------------------------------------

    public function test_enabling_stripe_with_both_credentials_makes_the_driver_ready()
    {
        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'enabled_gateways' => ['manual', 'stripe'],
            'gateway_credentials' => [
                'stripe' => ['secret_key' => 'sk_test_1', 'webhook_secret' => 'whsec_1'],
            ],
        ]))->assertRedirect();

        $this->actingAs($this->superadmin)
            ->get(route('admin.setting.store.show'))
            ->assertInertia(function ($page) {
                $stripe = collect($page->toArray()['props']['gateways'])->firstWhere('key', 'stripe');
                $this->assertTrue($stripe['is_configured']);
            });
    }

    public function test_a_gateway_switched_on_without_credentials_is_not_reported_ready()
    {
        $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), $this->payload([
            'enabled_gateways' => ['stripe'],
            'gateway_credentials' => ['stripe' => ['secret_key' => 'sk_test_1']],
        ]))->assertRedirect();

        $this->actingAs($this->superadmin)
            ->get(route('admin.setting.store.show'))
            ->assertInertia(function ($page) {
                $stripe = collect($page->toArray()['props']['gateways'])->firstWhere('key', 'stripe');
                $this->assertFalse($stripe['is_configured'], 'A half-configured gateway must never be offered.');
            });
    }

    public function test_an_unknown_gateway_key_is_rejected()
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.setting.store.update'), $this->payload(['enabled_gateways' => ['notagateway']]))
            ->assertSessionHasErrors('enabled_gateways.0');
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

    public function test_the_page_exposes_the_webhook_url_for_each_gateway()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.setting.store.show'))
            ->assertInertia(fn ($page) => $page->where(
                'webhookUrlTemplate',
                route('api.store.webhook', ['gateway' => '__GATEWAY__'])
            ));
    }
}

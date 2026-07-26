<?php

namespace Tests\Feature\Store;

use App\Models\StoreCurrency;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin → Store → Payment Gateways.
 *
 * The page is generated entirely from each driver's settingsSchema(), so these tests also cover
 * the promise that registering a new gateway needs no UI work.
 */
class StorePaymentGatewayAdminTest extends TestCase
{
    use RefreshDatabase;

    private const MASK = '********';

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
            'enabled_gateways' => ['manual'],
            'gateway_credentials' => [],
        ], $overrides);
    }

    private function stripeConfigured(): void
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual', 'stripe'];
        $settings->gateway_credentials = [
            'stripe' => ['secret_key' => 'sk_test_original', 'webhook_secret' => 'whsec_original'],
        ];
        $settings->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function gatewayProp(string $key, $page): array
    {
        return collect($page->toArray()['props']['gateways'])->firstWhere('key', $key);
    }

    // --- Access ------------------------------------------------------------------------------

    public function test_a_guest_is_redirected_to_login()
    {
        $this->get(route('admin.store.payment-gateway.index'))->assertRedirect(route('login'));
    }

    public function test_a_user_without_the_settings_permission_is_forbidden()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.payment-gateway.index'))
            ->assertForbidden();
    }

    public function test_a_superadmin_sees_every_registered_gateway()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertOk()
            ->assertInertia(function ($page) {
                $page->component('Admin/StorePaymentGateway/IndexStorePaymentGateway');

                $keys = collect($page->toArray()['props']['gateways'])->pluck('key');

                $this->assertEqualsCanonicalizing(array_keys(config('store.gateways')), $keys->all());
            });
    }

    // --- Secrets never round-trip ---------------------------------------------------------------

    public function test_a_stored_secret_is_masked_rather_than_sent()
    {
        $this->stripeConfigured();

        $response = $this->actingAs($this->superadmin)->get(route('admin.store.payment-gateway.index'));

        $response->assertOk();
        $response->assertDontSee('sk_test_original');
        $response->assertDontSee('whsec_original');

        $response->assertInertia(function ($page) {
            $stripe = $this->gatewayProp('stripe', $page);

            $this->assertEquals(self::MASK, $stripe['credentials']['secret_key']);
            $this->assertEquals(self::MASK, $stripe['credentials']['webhook_secret']);
        });
    }

    public function test_an_unset_secret_is_sent_as_null_rather_than_a_mask()
    {
        // A masked empty field would read as "already configured" and be impossible to fill in.
        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(function ($page) {
                $this->assertNull($this->gatewayProp('stripe', $page)['credentials']['secret_key']);
            });
    }

    public function test_a_secret_submitted_unchanged_as_the_mask_is_kept()
    {
        $this->stripeConfigured();

        $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), $this->payload([
            'enabled_gateways' => ['manual', 'stripe'],
            'gateway_credentials' => [
                'stripe' => ['secret_key' => self::MASK, 'webhook_secret' => 'whsec_rotated'],
            ],
        ]))->assertRedirect();

        $stored = app(StoreSettings::class)->refresh()->gateway_credentials;

        $this->assertEquals('sk_test_original', $stored['stripe']['secret_key'], 'An untouched secret must survive the round trip.');
        $this->assertEquals('whsec_rotated', $stored['stripe']['webhook_secret'], 'A changed secret must be written.');
    }

    public function test_only_fields_a_driver_declares_are_stored()
    {
        $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), $this->payload([
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
        $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), $this->payload([
            'gateway_credentials' => ['notagateway' => ['token' => 'x']],
        ]))->assertRedirect();

        $this->assertArrayNotHasKey('notagateway', app(StoreSettings::class)->refresh()->gateway_credentials);
    }

    // --- Enabling -------------------------------------------------------------------------------

    public function test_enabling_stripe_with_both_credentials_makes_the_driver_ready()
    {
        $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), $this->payload([
            'enabled_gateways' => ['manual', 'stripe'],
            'gateway_credentials' => [
                'stripe' => ['secret_key' => 'sk_test_1', 'webhook_secret' => 'whsec_1'],
            ],
        ]))->assertRedirect();

        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(function ($page) {
                $stripe = $this->gatewayProp('stripe', $page);

                $this->assertTrue($stripe['is_enabled']);
                $this->assertTrue($stripe['is_configured']);
            });
    }

    public function test_a_gateway_switched_on_without_credentials_is_not_reported_ready()
    {
        $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), $this->payload([
            'enabled_gateways' => ['stripe'],
            'gateway_credentials' => ['stripe' => ['secret_key' => 'sk_test_1']],
        ]))->assertRedirect();

        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(function ($page) {
                $stripe = $this->gatewayProp('stripe', $page);

                $this->assertTrue($stripe['is_enabled'], 'The admin did switch it on…');
                $this->assertFalse($stripe['is_configured'], '…but a half-configured gateway must never be offered.');
            });
    }

    public function test_switching_a_gateway_off_leaves_its_credentials_in_place()
    {
        // Turning a gateway off is not the same as forgetting its keys.
        $this->stripeConfigured();

        $this->actingAs($this->superadmin)
            ->post(route('admin.store.payment-gateway.update'), $this->payload(['enabled_gateways' => ['manual']]));

        $settings = app(StoreSettings::class)->refresh();

        $this->assertEquals(['manual'], $settings->enabled_gateways);
        $this->assertEquals('sk_test_original', $settings->gateway_credentials['stripe']['secret_key']);
    }

    public function test_an_unknown_gateway_key_is_rejected()
    {
        $this->actingAs($this->superadmin)
            ->post(route('admin.store.payment-gateway.update'), $this->payload(['enabled_gateways' => ['notagateway']]))
            ->assertSessionHasErrors('enabled_gateways.0');
    }

    // --- Operator guidance ------------------------------------------------------------------------

    public function test_each_gateway_carries_its_own_webhook_url()
    {
        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(function ($page) {
                foreach ($page->toArray()['props']['gateways'] as $gateway) {
                    $this->assertEquals(
                        route('api.store.webhook', ['gateway' => $gateway['key']]),
                        $gateway['webhook_url']
                    );
                }
            });
    }

    public function test_a_currency_a_gateway_cannot_charge_is_called_out()
    {
        // Nothing registered restricts its currencies today, so this asserts the shape rather than
        // a specific driver: a driver that answers null to supportedCurrencies() flags nothing.
        StoreCurrency::factory()->create(['code' => 'JPY', 'is_enabled' => true, 'is_base' => false]);

        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(function ($page) {
                $stripe = $this->gatewayProp('stripe', $page);

                $this->assertNull($stripe['supported_currencies']);
                $this->assertSame([], $stripe['unsupported_currencies']);
            });
    }

    public function test_the_page_warns_when_no_currency_is_enabled()
    {
        StoreCurrency::query()->update(['is_enabled' => false]);

        $this->actingAs($this->superadmin)
            ->get(route('admin.store.payment-gateway.index'))
            ->assertInertia(fn ($page) => $page->has('enabledCurrencies', 0));
    }
}

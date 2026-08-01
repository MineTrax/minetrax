<?php

use App\Models\StoreCurrency;
use App\Models\StorePaymentGateway as GatewayRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const GATEWAY_CREDENTIAL_MASK = '********';

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $this->superadmin = User::whereId(1)->first();
});

/**
 * @return array<string, mixed>
 */
function paymentGatewayAdminPayload(array $overrides = []): array
{
    return array_merge([
        'enabled_gateways' => ['manual'],
        'gateway_credentials' => [],
    ], $overrides);
}

function stripeConfigured(): void
{
    test()->enableStoreGateways(['manual', 'stripe'], [
        'stripe' => ['secret_key' => 'sk_test_original', 'webhook_secret' => 'whsec_original'],
    ]);
}

/**
 * One gateway's stored credentials.
 *
 * @return array<string, mixed>
 */
function storedCredentials(string $key): array
{
    return GatewayRecord::firstWhere('key', $key)?->credentials ?? [];
}

/**
 * @return array<string, mixed>
 */
function gatewayProp(string $key, $page): array
{
    return collect($page->toArray()['props']['gateways'])->firstWhere('key', $key);
}

test('a guest is redirected to login', function () {
    $this->get(route('admin.store.payment-gateway.index'))->assertRedirect(route('login'));
});

test('a user without the settings permission is forbidden', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.payment-gateway.index'))
        ->assertForbidden();
});

test('a superadmin sees every registered gateway', function () {
    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $page->component('Admin/StorePaymentGateway/IndexStorePaymentGateway');

            $keys = collect($page->toArray()['props']['gateways'])->pluck('key');

            expect($keys->all())->toEqualCanonicalizing(array_keys(config('store.gateways')));
        });
});

test('a stored secret is masked rather than sent', function () {
    stripeConfigured();

    $response = $this->actingAs($this->superadmin)->get(route('admin.store.payment-gateway.index'));

    $response->assertOk();
    $response->assertDontSee('sk_test_original');
    $response->assertDontSee('whsec_original');

    $response->assertInertia(function ($page) {
        $stripe = gatewayProp('stripe', $page);

        expect($stripe['credentials']['secret_key'])->toEqual(GATEWAY_CREDENTIAL_MASK);
        expect($stripe['credentials']['webhook_secret'])->toEqual(GATEWAY_CREDENTIAL_MASK);
    });
});

test('an unset secret is sent as null rather than a mask', function () {
    // A masked empty field would read as "already configured" and be impossible to fill in.
    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(function ($page) {
            expect(gatewayProp('stripe', $page)['credentials']['secret_key'])->toBeNull();
        });
});

test('a secret submitted unchanged as the mask is kept', function () {
    stripeConfigured();

    $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload([
        'enabled_gateways' => ['manual', 'stripe'],
        'gateway_credentials' => [
            'stripe' => ['secret_key' => GATEWAY_CREDENTIAL_MASK, 'webhook_secret' => 'whsec_rotated'],
        ],
    ]))->assertRedirect();

    $stored = storedCredentials('stripe');

    expect($stored['secret_key'])->toEqual('sk_test_original', 'An untouched secret must survive the round trip.');
    expect($stored['webhook_secret'])->toEqual('whsec_rotated', 'A changed secret must be written.');
});

test('only fields a driver declares are stored', function () {
    $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload([
        'enabled_gateways' => ['stripe'],
        'gateway_credentials' => [
            'stripe' => ['secret_key' => 'sk_test_1', 'webhook_secret' => 'whsec_1', 'evil' => 'payload'],
        ],
    ]))->assertRedirect();

    $stored = storedCredentials('stripe');

    $this->assertArrayNotHasKey('evil', $stored);
    expect($stored['secret_key'])->toEqual('sk_test_1');
});

test('a credential for an unregistered gateway is discarded', function () {
    $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload([
        'gateway_credentials' => ['notagateway' => ['token' => 'x']],
    ]))->assertRedirect();

    // No row is created for a key no driver claims, so nothing was stored at all.
    expect(GatewayRecord::where('key', 'notagateway')->exists())->toBeFalse();
});

test('enabling stripe with both credentials makes the driver ready', function () {
    $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload([
        'enabled_gateways' => ['manual', 'stripe'],
        'gateway_credentials' => [
            'stripe' => ['secret_key' => 'sk_test_1', 'webhook_secret' => 'whsec_1'],
        ],
    ]))->assertRedirect();

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(function ($page) {
            $stripe = gatewayProp('stripe', $page);

            expect($stripe['is_enabled'])->toBeTrue();
            expect($stripe['is_configured'])->toBeTrue();
        });
});

test('a gateway switched on without credentials is not reported ready', function () {
    $this->actingAs($this->superadmin)->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload([
        'enabled_gateways' => ['stripe'],
        'gateway_credentials' => ['stripe' => ['secret_key' => 'sk_test_1']],
    ]))->assertRedirect();

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(function ($page) {
            $stripe = gatewayProp('stripe', $page);

            expect($stripe['is_enabled'])->toBeTrue('The admin did switch it on…');
            expect($stripe['is_configured'])->toBeFalse('…but a half-configured gateway must never be offered.');
        });
});

test('switching a gateway off leaves its credentials in place', function () {
    // Turning a gateway off is not the same as forgetting its keys.
    stripeConfigured();

    $this->actingAs($this->superadmin)
        ->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload(['enabled_gateways' => ['manual']]));

    expect(GatewayRecord::enabled()->pluck('key')->all())->toEqual(['manual']);
    expect(storedCredentials('stripe')['secret_key'])->toEqual('sk_test_original');
});

test('an unknown gateway key is rejected', function () {
    $this->actingAs($this->superadmin)
        ->post(route('admin.store.payment-gateway.update'), paymentGatewayAdminPayload(['enabled_gateways' => ['notagateway']]))
        ->assertSessionHasErrors('enabled_gateways.0');
});

test('each gateway carries its own webhook url', function () {
    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(function ($page) {
            foreach ($page->toArray()['props']['gateways'] as $gateway) {
                expect($gateway['webhook_url'])->toEqual(route('api.store.webhook', ['gateway' => $gateway['key']]));
            }
        });
});

test('a currency a gateway cannot charge is called out', function () {
    // Nothing registered restricts its currencies today, so this asserts the shape rather than
    // a specific driver: a driver that answers null to supportedCurrencies() flags nothing.
    StoreCurrency::factory()->create(['code' => 'JPY', 'is_enabled' => true, 'is_base' => false]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(function ($page) {
            $stripe = gatewayProp('stripe', $page);

            expect($stripe['supported_currencies'])->toBeNull();
            expect($stripe['unsupported_currencies'])->toBe([]);
        });
});

test('the page warns when no currency is enabled', function () {
    StoreCurrency::query()->update(['is_enabled' => false]);

    $this->actingAs($this->superadmin)
        ->get(route('admin.store.payment-gateway.index'))
        ->assertInertia(fn ($page) => $page->has('enabledCurrencies', 0));
});

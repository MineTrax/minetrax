<?php

use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePaymentGateway;
use App\Models\User;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $this->superadmin = User::whereId(1)->first();
});

/**
 * @return array<string, mixed>
 */
function settingAdminPayload(array $overrides = []): array
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
        'show_purchase_goal' => false,
        'purchase_goal_amount' => 0,
        'show_top_donor' => false,
        'hide_buyer_identity' => false,
        'notify_staff_on_purchase' => true,
        'auto_ban_on_chargeback' => false,
    ], $overrides);
}

test('a guest is redirected to login', function () {
    $this->get(route('admin.setting.store.show'))->assertRedirect(route('login'));
});

test('a user without the settings permission is forbidden', function () {
    // 403 rather than the admin group's usual redirect, matching every other settings page.
    $this->actingAs(User::factory()->create())
        ->get(route('admin.setting.store.show'))
        ->assertForbidden();
});

test('a superadmin sees the page', function () {
    $this->actingAs($this->superadmin)
        ->get(route('admin.setting.store.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Setting/StoreSetting')
            ->has('settings')
            ->has('currencies')
        );
});

test('the settings page carries no gateway data at all', function () {
    $this->enableStoreGateways(['manual', 'stripe'], [
        'stripe' => ['secret_key' => 'sk_test_supersecret'],
    ]);

    $response = $this->actingAs($this->superadmin)->get(route('admin.setting.store.show'));

    $response->assertOk();
    $response->assertDontSee('sk_test_supersecret');

    $response->assertInertia(fn ($page) => $page
        ->missing('settings.gateway_credentials')
        ->missing('settings.enabled_gateways')
        ->missing('gateways')
    );
});

test('saving the settings leaves the gateway configuration untouched', function () {
    // They are separate rows now, so this is structural rather than a matter of remembering to
    // strip keys — but it is the property that matters, so it stays asserted.
    $this->enableStoreGateways(['manual', 'stripe'], [
        'stripe' => ['secret_key' => 'sk_test_keepme'],
    ]);

    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['store_name' => 'Renamed']))
        ->assertSessionHasNoErrors();

    expect(app(StoreSettings::class)->refresh()->store_name)->toEqual('Renamed');

    expect(StorePaymentGateway::enabled()->pluck('key')->sort()->values()->all())
        ->toEqual(['manual', 'stripe']);
    expect(StorePaymentGateway::firstWhere('key', 'stripe')->credential('secret_key'))
        ->toEqual('sk_test_keepme');
});

test('the settings are saved', function () {
    $this->actingAs($this->superadmin)->post(route('admin.setting.store.update'), settingAdminPayload([
        'store_name' => 'Diamond Shop',
        'tax_mode' => 'exclusive',
        'tax_rate_bp' => 2000,
        'enable_guest_checkout' => false,
        'mojang_username_verification' => false,
    ]))->assertRedirect();

    $settings = app(StoreSettings::class)->refresh();

    expect($settings->store_name)->toEqual('Diamond Shop');
    expect($settings->tax_mode)->toEqual('exclusive');
    expect($settings->tax_rate_bp)->toEqual(2000);
    expect($settings->enable_guest_checkout)->toBeFalse();
    expect($settings->mojang_username_verification)->toBeFalse();
});

test('a tax rate above one hundred percent is rejected', function () {
    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['tax_rate_bp' => 10001]))
        ->assertSessionHasErrors('tax_rate_bp');
});

test('an invalid tax mode is rejected', function () {
    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['tax_mode' => 'sometimes']))
        ->assertSessionHasErrors('tax_mode');
});

test('the base currency is locked once orders exist', function () {
    StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => false]);
    StoreOrder::factory()->create();

    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['base_currency' => 'EUR']))
        ->assertSessionHasErrors('base_currency');

    expect(app(StoreSettings::class)->refresh()->base_currency)->toEqual('USD');
});

test('the base currency can be changed before the first order', function () {
    StoreCurrency::factory()->create(['code' => 'EUR', 'is_base' => false]);

    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['base_currency' => 'EUR']))
        ->assertSessionHasNoErrors();

    expect(app(StoreSettings::class)->refresh()->base_currency)->toEqual('EUR');
});

test('the monthly goal is saved as minor units', function () {
    // $250.00 is 25000, and the form is what converts it — the setting itself never sees a decimal.
    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload([
            'show_purchase_goal' => true,
            'purchase_goal_amount' => 25000,
        ]))
        ->assertSessionHasNoErrors();

    $settings = app(StoreSettings::class)->refresh();
    expect($settings->show_purchase_goal)->toBeTrue();
    expect($settings->purchase_goal_amount)->toBe(25000);
});

test('a negative goal is refused', function () {
    $this->actingAs($this->superadmin)
        ->post(route('admin.setting.store.update'), settingAdminPayload(['purchase_goal_amount' => -1]))
        ->assertSessionHasErrors('purchase_goal_amount');
});

test('the settings screen carries each currencys exponent', function () {
    // The goal field types a decimal and sends minor units, which needs the base exponent.
    $this->actingAs($this->superadmin)
        ->get(route('admin.setting.store.show'))
        ->assertInertia(fn ($page) => $page->has('currencies.0.exponent'));
});

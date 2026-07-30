<?php

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function setHomepage(string $route): void
{
    $settings = app(GeneralSettings::class);
    $settings->homepage_route = $route;
    $settings->save();
}

test('the default homepage is the community dashboard', function () {
    expect(app(GeneralSettings::class)->homepage_route)->toEqual('dashboard');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('the flag makes the store the homepage', function () {
    setHomepage('store');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Store/IndexStore'));
});

test('disabling the module falls the homepage back to the dashboard', function () {
    setHomepage('store');
    config(['store.enabled' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('the dashboard stays reachable when the store owns the root', function () {
    setHomepage('store');

    $this->get(route('home.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('the dashboard route works with the flag off too', function () {
    $this->get(route('home.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Dashboard'));
});

test('the store url redirects to the root when the flag is on', function () {
    // One canonical URL for the storefront, and every existing route('store.index') link
    // keeps working.
    setHomepage('store');

    $this->get(route('store.index'))
        ->assertStatus(301)
        ->assertRedirect(route('home'));
});

test('the store url renders normally when the flag is off', function () {
    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Store/IndexStore'));
});

test('the store url does not redirect when the module is disabled', function () {
    // Redirecting to a dashboard would be a confusing answer to "where is the store?".
    setHomepage('store');
    config(['store.enabled' => false]);

    $this->get(route('store.index'))->assertForbidden();
});

test('the shared prop tells the frontend the store owns the root', function () {
    setHomepage('store');

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('store.isHomepage', true));

    setHomepage('dashboard');

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('store.isHomepage', false));
});

// --- Default navbar -------------------------------------------------------------------------
/**
 * The navbar is JSON-encoded into window._customnav by a Blade component rather than shipped as
 * an Inertia prop, so it is read off the rendered page.
 *
 * @return array<int, array<string, mixed>>
 */
function defaultNavbarLeft(): array
{
    $html = test()->get(route('home'))->assertOk()->getContent();

    preg_match('/window\._customnav = (.*?);\s*\n/', $html, $matches);

    expect($matches)->not->toBeEmpty('The navbar payload was not found on the page.');

    return json_decode(html_entity_decode($matches[1], ENT_QUOTES), true)['data']['left'];
}

test('store is the first navbar item after the logo', function () {
    $left = defaultNavbarLeft();

    expect($left[0]['name'])->toBe('App Logo');
    expect($left[1]['name'])->toBe('Store', 'Store leads the navbar.');
    expect($left[2]['name'])->toBe('Statistics', 'Statistics follows it.');
});

test('the navbar store item points at the storefront', function () {
    expect(defaultNavbarLeft()[1]['route'])->toBe('store.index');
});

test('the navbar store item points at the root when the store owns it', function () {
    // store.index only 301s to `/` in this case, so linking there directly saves the hop.
    setHomepage('store');

    expect(defaultNavbarLeft()[1]['route'])->toBe('home');
});

test('a dashboard item appears only when the store owns the root', function () {
    expect(collect(defaultNavbarLeft())->pluck('name')->all())->not->toContain('Dashboard');

    setHomepage('store');

    $left = defaultNavbarLeft();

    expect($left[2]['name'])->toBe('Dashboard', 'Otherwise nothing points at the community homepage.');
    expect($left[2]['route'])->toBe('home.dashboard');
});

test('the navbar has no store item when the module is disabled', function () {
    config(['store.enabled' => false]);

    $names = collect(defaultNavbarLeft())->pluck('name')->all();

    expect($names)->not->toContain('Store');
    expect($names[1])->toBe('Statistics');
});

test('an admin can switch the homepage to the store', function () {
    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.setting.general.update'), generalPayload(['homepage_route' => 'store']))
        ->assertSessionHasNoErrors();

    expect(app(GeneralSettings::class)->refresh()->homepage_route)->toEqual('store');
});

test('the store option is not offered while the module is disabled', function () {
    config(['store.enabled' => false]);

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.setting.general.show'))
        ->assertInertia(function ($page) {
            $options = $page->toArray()['props']['homepageOptions'];

            expect($options)->toHaveKey('dashboard');
            $this->assertArrayNotHasKey('store', $options);
        });
});

test('selecting the store while the module is disabled falls back', function () {
    config(['store.enabled' => false]);

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.setting.general.update'), generalPayload(['homepage_route' => 'store']));

    expect(app(GeneralSettings::class)->refresh()->homepage_route)->toEqual('dashboard');
});

test('a null homepage keeps whatever was already set', function () {
    // The select no longer offers a blank option, but the rule is `nullable` for the older-client
    // case the controller guards. Null must not silently reset the site's front page.
    $settings = app(GeneralSettings::class);
    $settings->homepage_route = 'store';
    $settings->save();

    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.setting.general.update'), generalPayload(['homepage_route' => null]))
        ->assertSessionHasNoErrors();

    expect(app(GeneralSettings::class)->refresh()->homepage_route)->toEqual('store');
});

test('an unknown homepage value is rejected', function () {
    $this->actingAs(User::whereId(1)->first())
        ->post(route('admin.setting.general.update'), generalPayload(['homepage_route' => 'somewhere-else']))
        ->assertSessionHasErrors('homepage_route');
});

/**
 * @return array<string, mixed>
 */
function generalPayload(array $overrides = []): array
{
    $settings = app(GeneralSettings::class);

    return array_merge($settings->toArray(), [
        'site_name' => $settings->site_name ?: 'MineTrax',
        'homepage_route' => 'dashboard',
    ], $overrides);
}

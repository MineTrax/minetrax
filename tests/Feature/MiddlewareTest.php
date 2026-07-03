<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Middleware Tests
|--------------------------------------------------------------------------
|
| Tests for custom middleware aliases registered in bootstrap/app.php:
| forbid-banned-user, forbid-muted-user, redirect-uncompleted-user,
| staff-member, auth.api-key, verified-if-enabled
|
*/

// ── forbid-banned-user ──────────────────────────────────────────────

test('banned user sees banned page on homepage', function () {
    $user = User::factory()->create(['banned_at' => now()]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page->component('ShowBannedPage', false));
});

test('banned user is logged out when visiting homepage', function () {
    $user = User::factory()->create(['banned_at' => now()]);

    $this->actingAs($user)
        ->get(route('home'));

    $this->assertGuest();
});

test('non-banned user can access homepage normally', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Dashboard', false));
});

// ── staff-member ────────────────────────────────────────────────────

test('staff member middleware allows admin users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

test('staff member middleware allows moderator users', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

test('staff member middleware blocks regular users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('staff member middleware redirects guests', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect();
});

// ── auth:sanctum ────────────────────────────────────────────────────

test('auth protected routes redirect guests', function () {
    $this->get(route('notification.index'))
        ->assertRedirect();
});

test('authenticated user can access auth protected routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('notification.index'))
        ->assertStatus(200);
});

// ── Public routes (no auth required) ────────────────────────────────

test('guest can access homepage', function () {
    $this->get(route('home'))
        ->assertStatus(200);
});

test('guest can access news index', function () {
    $this->get(route('news.index'))
        ->assertStatus(200);
});

test('guest can access player stats', function () {
    $this->get(route('player.index'))
        ->assertStatus(200);
});

test('guest can access staff members page', function () {
    $this->get(route('staff.index'))
        ->assertStatus(200);
});

test('guest can access features page', function () {
    $this->get(route('features.list'))
        ->assertStatus(200);
});

// ── Inertia shared data ─────────────────────────────────────────────

test('inertia shares expected global data for guests', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->has('appName')
            ->has('locale')
            ->has('webVersion')
            ->has('generalSettings')
            ->has('showPoweredBy')
            ->has('hasRegistrationFeature')
        );
});

test('inertia shares permissions for authenticated users', function () {
    $this->actingAs(User::whereId(1)->first())
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->has('permissions')
            ->where('permissions', fn ($permissions) => count($permissions) > 0)
        );
});

test('inertia shares empty permissions for guests', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('permissions', [])
        );
});

// ── Cookie consent ──────────────────────────────────────────────────

test('cookie consent is shown when no consent cookie is present', function () {
    config(['minetrax.cookie_consent_enabled' => true]);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showCookieConsent', true));
});

test('cookie consent is hidden when the plaintext consent cookie is present', function () {
    config(['minetrax.cookie_consent_enabled' => true]);

    $this->withUnencryptedCookie('laravel_cookie_consent', '1')
        ->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showCookieConsent', false));
});

test('cookie consent is hidden when the feature is disabled', function () {
    config(['minetrax.cookie_consent_enabled' => false]);

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->where('showCookieConsent', false));
});

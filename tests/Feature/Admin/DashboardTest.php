<?php

use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();
});

test('superadmin can view admin dashboard', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

test('admin dashboard returns correct inertia component', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page->component('Admin/Dashboard', false));
});

test('admin dashboard shares expected props', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->has('kpiTotalUsers')
        );
});

test('admin user can access dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

test('moderator can access dashboard', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $this->actingAs($moderator)
        ->get(route('admin.dashboard'))
        ->assertStatus(200);
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('guest is redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('schedule list command works after kernel migration', function () {
    $this->artisan('schedule:list')
        ->assertSuccessful();
});

test('route list command works', function () {
    $this->artisan('route:list')
        ->assertSuccessful();
});

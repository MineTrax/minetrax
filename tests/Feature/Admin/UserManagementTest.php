<?php

use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();
});

test('superadmin can list users', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.user.index'))
        ->assertStatus(200);
});

test('superadmin can filter users by search query', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.user.index', ['filter' => ['q' => 'admin']]))
        ->assertStatus(200);
});

test('superadmin can sort users', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.user.index', ['sort' => '-created_at']))
        ->assertStatus(200);
});

test('superadmin can view user edit page', function () {
    $user = User::factory()->create();

    $this->actingAs($this->superAdmin)
        ->get(route('admin.user.edit', $user))
        ->assertStatus(200);
});

test('superadmin can update a user', function () {
    $user = User::factory()->create(['username' => 'testuser123']);

    $this->actingAs($this->superAdmin)
        ->put(route('admin.user.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'username' => 'testuser123',
            'role' => 'default',
            'show_yob' => false,
            'show_gender' => false,
            'verified' => true,
            'country_id' => $user->country_id,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($user->fresh()->name)->toBe('Updated Name');
});

test('superadmin can ban a user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->superAdmin)
        ->post(route('admin.user.ban', $user))
        ->assertRedirect();

    expect($user->fresh()->banned_at)->not->toBeNull();
});

test('superadmin can unban a user', function () {
    $user = User::factory()->create(['banned_at' => now()]);

    $this->actingAs($this->superAdmin)
        ->post(route('admin.user.unban', $user))
        ->assertRedirect();

    expect($user->fresh()->banned_at)->toBeNull();
});

test('superadmin can mute a user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->superAdmin)
        ->post(route('admin.user.mute', $user))
        ->assertRedirect();

    expect($user->fresh()->muted_at)->not->toBeNull();
});

test('superadmin can unmute a user', function () {
    $user = User::factory()->create(['muted_at' => now()]);

    $this->actingAs($this->superAdmin)
        ->post(route('admin.user.unmute', $user))
        ->assertRedirect();

    expect($user->fresh()->muted_at)->toBeNull();
});

test('regular user cannot list users in admin', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.user.index'))
        ->assertRedirect();
});

test('regular user cannot ban other users', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.user.ban', $target))
        ->assertRedirect();

    expect($target->fresh()->banned_at)->toBeNull();
});

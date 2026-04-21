<?php

use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();
});

test('superadmin can list roles', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.role.index'))
        ->assertStatus(200);
});

test('superadmin can view role create form', function () {
    $this->actingAs($this->superAdmin)
        ->get(route('admin.role.create'))
        ->assertStatus(200);
});

test('superadmin can create a role with permissions', function () {
    $this->actingAs($this->superAdmin)
        ->post(route('admin.role.store'), [
            'name' => 'helper',
            'display_name' => 'Helper',
            'is_staff' => false,
            'is_hidden_from_staff_list' => false,
            'weight' => 1,
            'permissions' => ['read users', 'mute users'],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('roles', ['name' => 'helper', 'display_name' => 'Helper']);

    $role = Role::where('name', 'helper')->first();
    expect($role->hasPermissionTo('read users'))->toBeTrue();
    expect($role->hasPermissionTo('mute users'))->toBeTrue();
    expect($role->hasPermissionTo('delete users'))->toBeFalse();
});

test('seeded roles exist and have correct staff status', function () {
    $superadmin = Role::where('name', 'superadmin')->first();
    $admin = Role::where('name', 'admin')->first();
    $moderator = Role::where('name', 'moderator')->first();
    $default = Role::where('name', 'default')->first();

    expect($superadmin)->not->toBeNull();
    expect($admin)->not->toBeNull();
    expect($moderator)->not->toBeNull();
    expect($default)->not->toBeNull();

    expect($superadmin->is_staff)->toBeTrue();
    expect($admin->is_staff)->toBeTrue();
    expect($moderator->is_staff)->toBeTrue();
    expect($default->is_staff)->toBeFalsy();
});

test('role weights are ordered correctly', function () {
    $superadmin = Role::where('name', 'superadmin')->first();
    $admin = Role::where('name', 'admin')->first();
    $moderator = Role::where('name', 'moderator')->first();
    $default = Role::where('name', 'default')->first();

    expect($superadmin->weight)->toBeGreaterThan($admin->weight);
    expect($admin->weight)->toBeGreaterThan($moderator->weight);
    expect($moderator->weight)->toBeGreaterThan($default->weight);
});

test('regular user cannot access role management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.role.index'))
        ->assertRedirect();
});

test('regular user cannot create roles', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.role.store'), [
            'name' => 'hacker',
            'display_name' => 'Hacker',
            'is_staff' => true,
            'weight' => 100,
        ])
        ->assertRedirect();

    $this->assertDatabaseMissing('roles', ['name' => 'hacker']);
});

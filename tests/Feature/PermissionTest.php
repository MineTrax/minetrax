<?php

use App\Models\User;

beforeEach(function () {
    $this->superAdmin = User::whereId(1)->first();
});

test('superadmin has superadmin role', function () {
    expect($this->superAdmin->hasRole('superadmin'))->toBeTrue();
});

test('superadmin has all permissions', function () {
    expect($this->superAdmin->can('create servers'))->toBeTrue();
    expect($this->superAdmin->can('read servers'))->toBeTrue();
    expect($this->superAdmin->can('update servers'))->toBeTrue();
    expect($this->superAdmin->can('delete servers'))->toBeTrue();
    expect($this->superAdmin->can('read users'))->toBeTrue();
    expect($this->superAdmin->can('update users'))->toBeTrue();
    expect($this->superAdmin->can('delete users'))->toBeTrue();
    expect($this->superAdmin->can('create news'))->toBeTrue();
    expect($this->superAdmin->can('create polls'))->toBeTrue();
    expect($this->superAdmin->can('view_admin_dashboard'))->toBeTrue();
    expect($this->superAdmin->can('ban users'))->toBeTrue();
    expect($this->superAdmin->can('mute users'))->toBeTrue();
});

test('default user role has no admin permissions', function () {
    $user = User::factory()->create();

    expect($user->hasRole('default'))->toBeTrue();
    expect($user->can('create servers'))->toBeFalse();
    expect($user->can('read users'))->toBeFalse();
    expect($user->can('delete users'))->toBeFalse();
    expect($user->can('create news'))->toBeFalse();
    expect($user->can('view_admin_dashboard'))->toBeFalse();
    expect($user->can('ban users'))->toBeFalse();
});

test('admin role has expected permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('create news'))->toBeTrue();
    expect($admin->can('read news'))->toBeTrue();
    expect($admin->can('update news'))->toBeTrue();
    expect($admin->can('delete news'))->toBeTrue();
    expect($admin->can('read users'))->toBeTrue();
    expect($admin->can('update users'))->toBeTrue();
    expect($admin->can('ban users'))->toBeTrue();
    expect($admin->can('mute users'))->toBeTrue();
});

test('admin role does not have server management permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('create servers'))->toBeFalse();
    expect($admin->can('delete servers'))->toBeFalse();
});

test('moderator role has limited permissions', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    expect($moderator->can('read users'))->toBeTrue();
    expect($moderator->can('mute users'))->toBeTrue();
    expect($moderator->can('warn users'))->toBeTrue();
    expect($moderator->can('delete shouts'))->toBeTrue();
    expect($moderator->can('delete comments'))->toBeTrue();
    expect($moderator->can('kill players'))->toBeTrue();
    expect($moderator->can('mute players'))->toBeTrue();
});

test('moderator role cannot manage news or servers', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    expect($moderator->can('create news'))->toBeFalse();
    expect($moderator->can('delete news'))->toBeFalse();
    expect($moderator->can('create servers'))->toBeFalse();
    expect($moderator->can('delete users'))->toBeFalse();
});

test('user can be assigned multiple roles', function () {
    $user = User::factory()->create();
    $user->assignRole('moderator');

    expect($user->hasRole('default'))->toBeTrue();
    expect($user->hasRole('moderator'))->toBeTrue();
    expect($user->can('mute users'))->toBeTrue();
});

test('user can be given direct permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create news');

    expect($user->can('create news'))->toBeTrue();
    expect($user->can('delete news'))->toBeFalse();
});

test('revoking permission works correctly', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('create news');
    expect($user->can('create news'))->toBeTrue();

    $user->revokePermissionTo('create news');
    expect($user->fresh()->can('create news'))->toBeFalse();
});

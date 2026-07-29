<?php

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin can list badges', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->get(route('admin.badge.index'));

    $response->assertStatus(200);
});

test('admin can view create form', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->get(route('admin.badge.create'));

    $response->assertStatus(200);
});

test('admin can create new badge', function () {
    Storage::fake('public');

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->post(route('admin.badge.store'), [
        'name' => 'Test Badge',
        'shortname' => 'test-badge',
        'is_sticky' => true,
        'sort_order' => 1,
        'photo' => UploadedFile::fake()->image('badge.jpg', 100, 100),
    ]);

    $this->assertDatabaseHas('badges', [
        'name' => 'Test Badge',
        'shortname' => 'test-badge',
        'is_sticky' => true,
        'sort_order' => 1,
    ]);

    $badge = Badge::where('shortname', 'test-badge')->first();
    expect($badge->getFirstMedia('badge'))->not->toBeNull();

    $response->assertRedirect(route('admin.badge.index'));
});

test('admin cannot create badge without required fields', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->post(route('admin.badge.store'), [
        'name' => '',
        'shortname' => '',
        'is_sticky' => true,
    ]);

    $response->assertSessionHasErrors(['name', 'shortname', 'photo']);
});

test('admin can update badge', function () {
    Storage::fake('public');

    $badge = Badge::factory()->create([
        'name' => 'Old Name',
        'shortname' => 'old-name',
        'is_sticky' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->put(route('admin.badge.update', $badge->id), [
        'name' => 'Updated Badge',
        'shortname' => 'updated-badge',
        'is_sticky' => true,
        'sort_order' => 2,
        'photo' => UploadedFile::fake()->image('new-badge.jpg', 100, 100),
    ]);

    $this->assertDatabaseHas('badges', [
        'id' => $badge->id,
        'name' => 'Updated Badge',
        'shortname' => 'updated-badge',
        'is_sticky' => true,
        'sort_order' => 2,
    ]);

    $badge->refresh();
    expect($badge->getFirstMedia('badge'))->not->toBeNull();

    $response->assertRedirect(route('admin.badge.index'));
});

test('admin can delete badge', function () {
    $badge = Badge::factory()->create();

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->delete(route('admin.badge.delete', $badge->id));

    $this->assertDatabaseMissing('badges', ['id' => $badge->id]);
    $response->assertRedirect();
});

test('admin can update badge without changing photo', function () {
    $badge = Badge::factory()->create([
        'name' => 'Old Name',
        'shortname' => 'old-name',
        'is_sticky' => false,
        'sort_order' => 1,
    ]);

    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->put(route('admin.badge.update', $badge->id), [
        'name' => 'Updated Badge',
        'shortname' => 'updated-badge',
        'is_sticky' => true,
        'sort_order' => 2,
    ]);

    $this->assertDatabaseHas('badges', [
        'id' => $badge->id,
        'name' => 'Updated Badge',
        'shortname' => 'updated-badge',
        'is_sticky' => true,
        'sort_order' => 2,
    ]);

    $response->assertRedirect(route('admin.badge.index'));
});

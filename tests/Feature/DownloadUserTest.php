<?php

use App\Models\Download;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view active download listing', function () {
    Download::factory()->create(['is_active' => true, 'is_only_auth' => false]);

    $response = $this->get(route('download.index'));
    $response->assertStatus(200);
});

test('guest can view single active download', function () {
    $download = Download::factory()->create(['is_active' => true, 'is_only_auth' => false]);

    $response = $this->get(route('download.show', $download->slug));
    $response->assertStatus(200);
});

test('auth only download not listed for guest', function () {
    $download = Download::factory()->create([
        'is_active' => true,
        'is_only_auth' => true,
    ]);

    $response = $this->get(route('download.index'));
    $response->assertStatus(200);
    $response->assertDontSee($download->name);
});

test('authenticated user can view auth only download', function () {
    $user = User::first();
    $download = Download::factory()->create([
        'is_active' => true,
        'is_only_auth' => true,
        'is_external' => true,
        'file_url' => 'https://example.com/file.zip',
        'file_name' => 'file.zip',
    ]);

    $this->actingAs($user);
    $response = $this->get(route('download.show', $download->slug));
    $response->assertStatus(200);
});

test('inactive download is not accessible', function () {
    $download = Download::factory()->create(['is_active' => false]);

    $response = $this->get(route('download.show', $download->slug));
    $response->assertStatus(403);
});

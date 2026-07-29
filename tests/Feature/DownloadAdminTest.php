<?php

use App\Models\Download;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access admin downloads', function () {
    $response = $this->get(route('admin.download.index'));
    $response->assertStatus(302);
});

test('admin can view download listing page', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->get(route('admin.download.index'));
    $response->assertStatus(200);
});

test('admin can create an external download', function () {
    $this->actingAs(User::whereId(1)->first());
    $data = [
        'name' => 'Test External Download',
        'description' => 'A test download file.',
        'is_external' => true,
        'file_url' => 'https://example.com/file.zip',
        'file_name' => 'file.zip',
        'is_external_url_hidden' => false,
        'is_only_auth' => false,
        'min_role_weight_required' => null,
        'is_active' => true,
    ];
    $response = $this->post(route('admin.download.store'), $data);

    $response->assertRedirect(route('admin.download.index'));
    $this->assertDatabaseHas('downloads', [
        'name' => 'Test External Download',
        'is_external' => true,
        'file_url' => 'https://example.com/file.zip',
    ]);
});

test('admin can update a download', function () {
    $this->actingAs(User::whereId(1)->first());
    $download = Download::factory()->create();

    $response = $this->put(route('admin.download.update', $download->id), [
        'name' => 'Updated Download Name',
        'description' => 'Updated description.',
        'is_external_url_hidden' => false,
        'file_name' => 'updated.zip',
        'is_only_auth' => true,
        'min_role_weight_required' => 5,
        'is_active' => false,
    ]);

    $response->assertRedirect(route('admin.download.index'));
    expect($download->fresh()->name)->toEqual('Updated Download Name');
    expect($download->fresh()->is_only_auth)->toBeTrue();
    expect($download->fresh()->is_active)->toBeFalse();
});

test('admin can delete a download', function () {
    $this->actingAs(User::whereId(1)->first());
    $download = Download::factory()->create();

    $this->assertDatabaseHas('downloads', $download->only('id', 'name'));
    $this->delete(route('admin.download.delete', $download->id));
    $this->assertDatabaseMissing('downloads', $download->only('id', 'name'));
});

test('download name is required', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->post(route('admin.download.store'), [
        'name' => '',
        'is_external' => true,
        'file_url' => 'https://example.com/file.zip',
        'file_name' => 'file.zip',
        'is_external_url_hidden' => false,
        'is_only_auth' => false,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('download name must be unique', function () {
    $this->actingAs(User::whereId(1)->first());
    Download::factory()->create(['name' => 'Unique Name']);

    $response = $this->post(route('admin.download.store'), [
        'name' => 'Unique Name',
        'is_external' => true,
        'file_url' => 'https://example.com/file.zip',
        'file_name' => 'file.zip',
        'is_external_url_hidden' => false,
        'is_only_auth' => false,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('non admin cannot access admin download routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.download.index'))->assertStatus(302);
    $this->get(route('admin.download.create'))->assertStatus(302);
});

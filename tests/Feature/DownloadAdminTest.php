<?php

namespace Tests\Feature;

use App\Models\Download;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_downloads()
    {
        $response = $this->get(route('admin.download.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_download_listing_page()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->get(route('admin.download.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_an_external_download()
    {
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
    }

    public function test_admin_can_update_a_download()
    {
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
        $this->assertEquals('Updated Download Name', $download->fresh()->name);
        $this->assertTrue($download->fresh()->is_only_auth);
        $this->assertFalse($download->fresh()->is_active);
    }

    public function test_admin_can_delete_a_download()
    {
        $this->actingAs(User::whereId(1)->first());
        $download = Download::factory()->create();

        $this->assertDatabaseHas('downloads', $download->only('id', 'name'));
        $this->delete(route('admin.download.delete', $download->id));
        $this->assertDatabaseMissing('downloads', $download->only('id', 'name'));
    }

    public function test_download_name_is_required()
    {
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
    }

    public function test_download_name_must_be_unique()
    {
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
    }

    public function test_non_admin_cannot_access_admin_download_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.download.index'))->assertStatus(302);
        $this->get(route('admin.download.create'))->assertStatus(302);
    }
}

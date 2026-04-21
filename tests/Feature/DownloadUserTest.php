<?php

namespace Tests\Feature;

use App\Models\Download;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_active_download_listing()
    {
        Download::factory()->create(['is_active' => true, 'is_only_auth' => false]);

        $response = $this->get(route('download.index'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_single_active_download()
    {
        $download = Download::factory()->create(['is_active' => true, 'is_only_auth' => false]);

        $response = $this->get(route('download.show', $download->slug));
        $response->assertStatus(200);
    }

    public function test_auth_only_download_not_listed_for_guest()
    {
        $download = Download::factory()->create([
            'is_active' => true,
            'is_only_auth' => true,
        ]);

        $response = $this->get(route('download.index'));
        $response->assertStatus(200);
        $response->assertDontSee($download->name);
    }

    public function test_authenticated_user_can_view_auth_only_download()
    {
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
    }

    public function test_inactive_download_is_not_accessible()
    {
        $download = Download::factory()->create(['is_active' => false]);

        $response = $this->get(route('download.show', $download->slug));
        $response->assertStatus(403);
    }
}

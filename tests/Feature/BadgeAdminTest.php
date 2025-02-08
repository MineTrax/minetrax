<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BadgeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_badges()
    {
        $this->actingAs(User::whereId(1)->first());  // Super admin
        $response = $this->get(route('admin.badge.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_form()
    {
        $this->actingAs(User::whereId(1)->first());  // Super admin
        $response = $this->get(route('admin.badge.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_new_badge()
    {
        Storage::fake('public');

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->post(route('admin.badge.store'), [
            'name' => 'Test Badge',
            'shortname' => 'test-badge',
            'is_sticky' => true,
            'sort_order' => 1,
            'photo' => UploadedFile::fake()->image('badge.jpg', 100, 100)
        ]);

        $this->assertDatabaseHas('badges', [
            'name' => 'Test Badge',
            'shortname' => 'test-badge',
            'is_sticky' => true,
            'sort_order' => 1
        ]);

        $badge = Badge::where('shortname', 'test-badge')->first();
        $this->assertNotNull($badge->getFirstMedia('badge'));

        $response->assertRedirect(route('admin.badge.index'));
    }

    public function test_admin_cannot_create_badge_without_required_fields()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->post(route('admin.badge.store'), [
            'name' => '',
            'shortname' => '',
            'is_sticky' => true
        ]);

        $response->assertSessionHasErrors(['name', 'shortname', 'photo']);
    }

    public function test_admin_can_update_badge()
    {
        Storage::fake('public');

        $badge = Badge::factory()->create([
            'name' => 'Old Name',
            'shortname' => 'old-name',
            'is_sticky' => false,
            'sort_order' => 1
        ]);

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->put(route('admin.badge.update', $badge->id), [
            'name' => 'Updated Badge',
            'shortname' => 'updated-badge',
            'is_sticky' => true,
            'sort_order' => 2,
            'photo' => UploadedFile::fake()->image('new-badge.jpg', 100, 100)
        ]);

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'name' => 'Updated Badge',
            'shortname' => 'updated-badge',
            'is_sticky' => true,
            'sort_order' => 2
        ]);

        $badge->refresh();
        $this->assertNotNull($badge->getFirstMedia('badge'));

        $response->assertRedirect(route('admin.badge.index'));
    }

    public function test_admin_can_delete_badge()
    {
        $badge = Badge::factory()->create();

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->delete(route('admin.badge.delete', $badge->id));

        $this->assertDatabaseMissing('badges', ['id' => $badge->id]);
        $response->assertRedirect();
    }

    public function test_admin_can_update_badge_without_changing_photo()
    {
        $badge = Badge::factory()->create([
            'name' => 'Old Name',
            'shortname' => 'old-name',
            'is_sticky' => false,
            'sort_order' => 1
        ]);

        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->put(route('admin.badge.update', $badge->id), [
            'name' => 'Updated Badge',
            'shortname' => 'updated-badge',
            'is_sticky' => true,
            'sort_order' => 2
        ]);

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'name' => 'Updated Badge',
            'shortname' => 'updated-badge',
            'is_sticky' => true,
            'sort_order' => 2
        ]);

        $response->assertRedirect(route('admin.badge.index'));
    }
}
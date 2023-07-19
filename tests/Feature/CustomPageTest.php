<?php

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_admin_section_cp_listing_page()
    {
        $response = $this->get(route('admin.custom-page.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_admin_section_cp_listing_page()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $response = $this->get(route('admin.custom-page.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_custom_page()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $data = [
            'title' => 'Page Test title for testing',
            'path' => 'page-title-url',
            'body' => 'Page Body must be little bit long?',
            'is_visible' => true,
            'is_in_navbar' => true,
            'is_redirect' => false,
            'redirect_url' => null,
            'is_html_page' => false,
            'is_open_in_new_tab' => false,
            'is_sidebar_visible' => true,
        ];
        $response = $this->post(route('admin.custom-page.store'), $data);
        $this->assertDatabaseHas('custom_pages', $data);
    }

    public function test_admin_can_update_custom_page()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $customPage = CustomPage::factory()->create();
        $response = $this->put(route('admin.custom-page.update', $customPage->id), [
            'title' => 'New Title',
            'path' => 'new-path',
            'body' => 'New Body',
            'is_visible' => false,
            'is_in_navbar' => false,
            'is_redirect' => false,
            'redirect_url' => null,
            'is_html_page' => false,
            'is_sidebar_visible' => false,
            'is_open_in_new_tab' => true,
        ]);

        $this->assertEquals('New Title', $customPage->fresh()->title);
        $this->assertEquals('new-path', $customPage->fresh()->path);
        $this->assertEquals('New Body', $customPage->fresh()->body);
        $this->assertEquals(false, $customPage->fresh()->is_visible);
        $this->assertEquals(false, $customPage->fresh()->is_in_navbar);
        $this->assertEquals(false, $customPage->fresh()->is_sidebar_visible);
        $this->assertEquals(false, $customPage->fresh()->is_html_page);
    }

    public function test_admin_can_delete_custom_page()
    {
        $this->actingAs(User::whereId(1)->first()); // Super admin
        $customPage = CustomPage::factory()->create();

        $this->assertDatabaseHas('custom_pages', $customPage->only('id', 'title', 'body'));
        $this->delete(route('admin.custom-page.delete', $customPage->id));

        $this->assertDatabaseMissing('custom_pages', $customPage->only('id', 'title', 'body'));
    }

    public function test_guest_can_view_custom_page()
    {
        $customPage = CustomPage::factory()->create();
        $response = $this->get(route('custom-page.show', $customPage->path));
        $response->assertStatus(200);
        $response->assertSee($customPage->title);
    }
}

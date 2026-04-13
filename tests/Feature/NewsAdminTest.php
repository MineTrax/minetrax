<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_news_listing()
    {
        $response = $this->get(route('admin.news.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_news_listing_page()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->get(route('admin.news.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_published_news()
    {
        $this->actingAs(User::whereId(1)->first());
        $data = [
            'title' => 'Test News Title',
            'body' => 'This is the body of the test news article.',
            'is_published' => true,
            'is_pinned' => false,
            'is_commentable' => true,
            'type' => 0,
        ];
        $response = $this->post(route('admin.news.store'), $data);

        $this->assertDatabaseHas('news', [
            'title' => 'Test News Title',
            'body' => 'This is the body of the test news article.',
            'is_pinned' => false,
            'is_commentable' => true,
        ]);

        $news = News::where('title', 'Test News Title')->first();
        $this->assertNotNull($news->published_at);
    }

    public function test_admin_can_create_unpublished_news()
    {
        $this->actingAs(User::whereId(1)->first());
        $data = [
            'title' => 'Draft News Title',
            'body' => 'This is a draft news article.',
            'is_published' => false,
            'is_pinned' => false,
            'is_commentable' => true,
            'type' => 0,
        ];
        $response = $this->post(route('admin.news.store'), $data);

        $news = News::where('title', 'Draft News Title')->first();
        $this->assertNotNull($news);
        $this->assertNull($news->published_at);
    }

    public function test_admin_can_update_news()
    {
        $this->actingAs(User::whereId(1)->first());
        $news = News::factory()->create();

        $response = $this->put(route('admin.news.update', $news->id), [
            'title' => 'Updated News Title',
            'body' => 'Updated body content.',
            'is_published' => true,
            'is_pinned' => true,
            'is_commentable' => false,
            'type' => 1,
        ]);

        $this->assertEquals('Updated News Title', $news->fresh()->title);
        $this->assertEquals('Updated body content.', $news->fresh()->body);
        $this->assertTrue($news->fresh()->is_pinned);
        $this->assertFalse($news->fresh()->is_commentable);
    }

    public function test_admin_can_delete_news()
    {
        $this->actingAs(User::whereId(1)->first());
        $news = News::factory()->create();

        $this->assertDatabaseHas('news', $news->only('id', 'title'));
        $this->delete(route('admin.news.delete', $news->id));
        $this->assertDatabaseMissing('news', $news->only('id', 'title'));
    }

    public function test_news_creation_requires_title_and_body()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->post(route('admin.news.store'), [
            'title' => '',
            'body' => '',
            'is_published' => true,
            'is_pinned' => false,
            'is_commentable' => true,
            'type' => 0,
        ]);

        $response->assertSessionHasErrors(['title', 'body']);
    }

    public function test_non_admin_user_cannot_access_admin_news_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.news.index'))->assertStatus(302);
        $this->get(route('admin.news.create'))->assertStatus(302);
    }
}

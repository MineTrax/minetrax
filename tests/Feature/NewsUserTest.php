<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_published_news_listing()
    {
        News::factory()->create(['published_at' => now()]);

        $response = $this->get(route('news.index'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_single_published_news()
    {
        $news = News::factory()->create(['published_at' => now()]);

        $response = $this->get(route('news.show', $news->slug));
        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_unpublished_news()
    {
        $news = News::factory()->create(['published_at' => null]);

        $response = $this->get(route('news.show', $news->slug));
        $response->assertStatus(403);
    }

    public function test_guest_can_list_comments_on_news()
    {
        $news = News::factory()->create(['published_at' => now()]);

        $response = $this->getJson(route('news.comment.index', $news->id));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_post_comment_on_commentable_news()
    {
        $user = User::first();
        $news = News::factory()->create([
            'published_at' => now(),
            'is_commentable' => true,
        ]);

        $this->actingAs($user);
        $response = $this->postJson(route('news.comment.store', $news->id), [
            'comment' => 'This is a test comment.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseHas('comments', [
            'comment' => 'This is a test comment.',
            'commentable_type' => News::class,
            'commentable_id' => $news->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_comment_on_non_commentable_news()
    {
        $user = User::first();
        $news = News::factory()->create([
            'published_at' => now(),
            'is_commentable' => false,
        ]);

        $this->actingAs($user);
        $response = $this->postJson(route('news.comment.store', $news->id), [
            'comment' => 'Should not be allowed.',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('comments', [
            'commentable_type' => News::class,
            'commentable_id' => $news->id,
        ]);
    }

    public function test_muted_user_cannot_post_comment()
    {
        $user = User::first();
        $user->muted_at = now();
        $user->save();

        $news = News::factory()->create([
            'published_at' => now(),
            'is_commentable' => true,
        ]);

        $this->actingAs($user);
        $response = $this->postJson(route('news.comment.store', $news->id), [
            'comment' => 'Muted user comment.',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment()
    {
        $user = User::first();
        $news = News::factory()->create([
            'published_at' => now(),
            'is_commentable' => true,
        ]);

        $comment = $news->commentAsUser($user, 'My comment to delete');

        $this->actingAs($user);
        $response = $this->delete(route('news.comment.delete', [$news->id, $comment->id]));

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $news = News::factory()->create([
            'published_at' => now(),
            'is_commentable' => true,
        ]);

        $comment = $news->commentAsUser($otherUser, 'Other user comment');

        $this->actingAs($user);
        $response = $this->delete(route('news.comment.delete', [$news->id, $comment->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}

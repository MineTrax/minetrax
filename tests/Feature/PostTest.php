<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_post_listing()
    {
        $response = $this->get(route('post.index'));
        $response->assertStatus(200);
    }

    public function test_guest_can_view_single_post()
    {
        $post = Post::factory()->create();

        $response = $this->get(route('post.show', $post->id));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson(route('post.store'), [
            'body' => 'This is a test post body.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseHas('posts', [
            'body' => 'This is a test post body.',
            'user_id' => $user->id,
        ]);
    }

    public function test_post_body_is_required_when_no_media()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson(route('post.store'), [
            'body' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_post_body_max_1000_chars()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->postJson(route('post.store'), [
            'body' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    public function test_muted_user_cannot_create_post()
    {
        $user = User::first();
        $user->muted_at = now();
        $user->save();

        $this->actingAs($user);
        $response = $this->postJson(route('post.store'), [
            'body' => 'Muted user post.',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_post()
    {
        $user = User::first();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->delete(route('post.delete', $post->id));

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_another_users_post()
    {
        $user = User::factory()->create();
        $otherUser = User::first();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);
        $response = $this->delete(route('post.delete', $post->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_guest_can_list_comments_on_post()
    {
        $post = Post::factory()->create();

        $response = $this->getJson(route('post.comment.index', $post->id));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_comment_on_post()
    {
        $user = User::first();
        $post = Post::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson(route('post.comment.store', $post->id), [
            'comment' => 'Nice post!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
        $this->assertDatabaseHas('comments', [
            'comment' => 'Nice post!',
            'commentable_type' => Post::class,
            'commentable_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_muted_user_cannot_comment_on_post()
    {
        $user = User::first();
        $user->muted_at = now();
        $user->save();

        $post = Post::factory()->create();

        $this->actingAs($user);
        $response = $this->postJson(route('post.comment.store', $post->id), [
            'comment' => 'Muted comment.',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_comment_on_post()
    {
        $user = User::first();
        $post = Post::factory()->create();

        $comment = $post->commentAsUser($user, 'My comment');

        $this->actingAs($user);
        $response = $this->delete(route('post.comment.delete', [$post->id, $comment->id]));

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment_on_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create();

        $comment = $post->commentAsUser($otherUser, 'Other user comment');

        $this->actingAs($user);
        $response = $this->delete(route('post.comment.delete', [$post->id, $comment->id]));

        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }
}

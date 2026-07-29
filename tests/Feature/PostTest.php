<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view post listing', function () {
    $response = $this->get(route('post.index'));
    $response->assertStatus(200);
});

test('guest can view single post', function () {
    $post = Post::factory()->create();

    $response = $this->get(route('post.show', $post->id));
    $response->assertStatus(200);
});

test('authenticated user can create post', function () {
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
});

test('post body is required when no media', function () {
    $user = User::first();
    $this->actingAs($user);

    $response = $this->postJson(route('post.store'), [
        'body' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('body');
});

test('post body max 1000 chars', function () {
    $user = User::first();
    $this->actingAs($user);

    $response = $this->postJson(route('post.store'), [
        'body' => str_repeat('a', 1001),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('body');
});

test('muted user cannot create post', function () {
    $user = User::first();
    $user->muted_at = now();
    $user->save();

    $this->actingAs($user);
    $response = $this->postJson(route('post.store'), [
        'body' => 'Muted user post.',
    ]);

    $response->assertStatus(403);
});

test('user can delete own post', function () {
    $user = User::first();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);
    $response = $this->delete(route('post.delete', $post->id));

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('user cannot delete another users post', function () {
    $user = User::factory()->create();
    $otherUser = User::first();
    $post = Post::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user);
    $response = $this->delete(route('post.delete', $post->id));

    $response->assertStatus(403);
    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

test('guest can list comments on post', function () {
    $post = Post::factory()->create();

    $response = $this->getJson(route('post.comment.index', $post->id));
    $response->assertStatus(200);
});

test('authenticated user can comment on post', function () {
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
});

test('muted user cannot comment on post', function () {
    $user = User::first();
    $user->muted_at = now();
    $user->save();

    $post = Post::factory()->create();

    $this->actingAs($user);
    $response = $this->postJson(route('post.comment.store', $post->id), [
        'comment' => 'Muted comment.',
    ]);

    $response->assertStatus(403);
});

test('user can delete own comment on post', function () {
    $user = User::first();
    $post = Post::factory()->create();

    $comment = $post->commentAsUser($user, 'My comment');

    $this->actingAs($user);
    $response = $this->delete(route('post.comment.delete', [$post->id, $comment->id]));

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('user cannot delete another users comment on post', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create();

    $comment = $post->commentAsUser($otherUser, 'Other user comment');

    $this->actingAs($user);
    $response = $this->delete(route('post.comment.delete', [$post->id, $comment->id]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('comments', ['id' => $comment->id]);
});

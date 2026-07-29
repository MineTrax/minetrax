<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can view published news listing', function () {
    News::factory()->create(['published_at' => now()]);

    $response = $this->get(route('news.index'));
    $response->assertStatus(200);
});

test('guest can view single published news', function () {
    $news = News::factory()->create(['published_at' => now()]);

    $response = $this->get(route('news.show', $news->slug));
    $response->assertStatus(200);
});

test('guest cannot view unpublished news', function () {
    $news = News::factory()->create(['published_at' => null]);

    $response = $this->get(route('news.show', $news->slug));
    $response->assertStatus(403);
});

test('guest can list comments on news', function () {
    $news = News::factory()->create(['published_at' => now()]);

    $response = $this->getJson(route('news.comment.index', $news->id));
    $response->assertStatus(200);
});

test('authenticated user can post comment on commentable news', function () {
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
});

test('user cannot comment on non commentable news', function () {
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
});

test('muted user cannot post comment', function () {
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
});

test('user can delete own comment', function () {
    $user = User::first();
    $news = News::factory()->create([
        'published_at' => now(),
        'is_commentable' => true,
    ]);

    $comment = $news->commentAsUser($user, 'My comment to delete');

    $this->actingAs($user);
    $response = $this->delete(route('news.comment.delete', [$news->id, $comment->id]));

    $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
});

test('user cannot delete another users comment', function () {
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
});

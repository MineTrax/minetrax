<?php

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access admin news listing', function () {
    $response = $this->get(route('admin.news.index'));
    $response->assertStatus(302);
});

test('admin can view news listing page', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->get(route('admin.news.index'));
    $response->assertStatus(200);
});

test('admin can create published news', function () {
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
    expect($news->published_at)->not->toBeNull();
});

test('admin can create unpublished news', function () {
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
    expect($news)->not->toBeNull();
    expect($news->published_at)->toBeNull();
});

test('admin can update news', function () {
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

    expect($news->fresh()->title)->toEqual('Updated News Title');
    expect($news->fresh()->body)->toEqual('Updated body content.');
    expect($news->fresh()->is_pinned)->toBeTrue();
    expect($news->fresh()->is_commentable)->toBeFalse();
});

test('admin can delete news', function () {
    $this->actingAs(User::whereId(1)->first());
    $news = News::factory()->create();

    $this->assertDatabaseHas('news', $news->only('id', 'title'));
    $this->delete(route('admin.news.delete', $news->id));
    $this->assertDatabaseMissing('news', $news->only('id', 'title'));
});

test('news creation requires title and body', function () {
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
});

test('non admin user cannot access admin news routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.news.index'))->assertStatus(302);
    $this->get(route('admin.news.create'))->assertStatus(302);
});

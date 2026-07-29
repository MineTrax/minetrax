<?php

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot view admin section cp listing page', function () {
    $response = $this->get(route('admin.custom-page.index'));
    $response->assertStatus(302);
});

test('admin can view admin section cp listing page', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $response = $this->get(route('admin.custom-page.index'));
    $response->assertStatus(200);
});

test('admin can create custom page', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
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
});

test('admin can update custom page', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
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

    expect($customPage->fresh()->title)->toEqual('New Title');
    expect($customPage->fresh()->path)->toEqual('new-path');
    expect($customPage->fresh()->body)->toEqual('New Body');
    expect($customPage->fresh()->is_visible)->toEqual(false);
    expect($customPage->fresh()->is_in_navbar)->toEqual(false);
    expect($customPage->fresh()->is_sidebar_visible)->toEqual(false);
    expect($customPage->fresh()->is_html_page)->toEqual(false);
});

test('admin can delete custom page', function () {
    $this->actingAs(User::whereId(1)->first());
    // Super admin
    $customPage = CustomPage::factory()->create();

    $this->assertDatabaseHas('custom_pages', $customPage->only('id', 'title', 'body'));
    $this->delete(route('admin.custom-page.delete', $customPage->id));

    $this->assertDatabaseMissing('custom_pages', $customPage->only('id', 'title', 'body'));
});

test('guest can view custom page', function () {
    $customPage = CustomPage::factory()->create();
    $response = $this->get(route('custom-page.show', $customPage->path));
    $response->assertStatus(200);
    $response->assertSee($customPage->title);
});

<?php

use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
});

function categoryAdminValidPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ranks',
        'description' => 'Server ranks and perks.',
        'parent_id' => null,
        'sort_order' => 0,
        'is_visible' => true,
        'is_enabled' => true,
        'display_type' => 'grid',
        'is_cumulative' => false,
    ], $overrides);
}

test('guest cannot access admin store categories', function () {
    $this->get(route('admin.store.category.index'))->assertStatus(302);
});

test('non staff user cannot access admin store categories', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.store.category.index'))->assertStatus(302);
    $this->get(route('admin.store.category.create'))->assertStatus(302);
});

test('admin can view the category listing', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCategory::factory()->count(3)->create();

    $this->get(route('admin.store.category.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Admin/StoreCategory/IndexStoreCategory', false));
});

test('admin can create a category', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryAdminValidPayload())
        ->assertRedirect(route('admin.store.category.index'));

    $this->assertDatabaseHas('store_categories', [
        'name' => 'Ranks',
        'slug' => 'ranks',
        'is_enabled' => true,
    ]);
});

test('slug is derived from the name', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryAdminValidPayload(['name' => 'VIP  Ranks & Perks']));

    $this->assertDatabaseHas('store_categories', ['slug' => 'vip-ranks-perks']);
});

test('duplicate slug is rejected', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCategory::factory()->create(['name' => 'Ranks', 'slug' => 'ranks']);

    $this->post(route('admin.store.category.store'), categoryAdminValidPayload())
        ->assertSessionHasErrors(['slug']);
});

test('name is required', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.category.store'), categoryAdminValidPayload(['name' => '']))
        ->assertSessionHasErrors(['name']);
});

test('admin can update a category', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();

    $this->put(route('admin.store.category.update', $category->id), categoryAdminValidPayload([
        'name' => 'Updated Name',
        'is_enabled' => false,
    ]))->assertRedirect(route('admin.store.category.index'));

    $category->refresh();
    expect($category->name)->toEqual('Updated Name');
    expect($category->slug)->toEqual('updated-name');
    expect($category->is_enabled)->toBeFalse();
});

test('updating a category keeps its own slug available', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create(['name' => 'Ranks', 'slug' => 'ranks']);

    $this->put(route('admin.store.category.update', $category->id), categoryAdminValidPayload([
        'name' => 'Ranks',
    ]))->assertSessionHasNoErrors();
});

test('a category cannot be its own parent', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();

    $this->put(route('admin.store.category.update', $category->id), categoryAdminValidPayload([
        'parent_id' => $category->id,
    ]))->assertSessionHasErrors(['parent_id']);
});

test('admin can delete a category without destroying its packages', function () {
    $this->actingAs(User::whereId(1)->first());
    $category = StoreCategory::factory()->create();
    $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

    $this->delete(route('admin.store.category.delete', $category->id));

    $this->assertDatabaseMissing('store_categories', ['id' => $category->id]);
    $this->assertDatabaseHas('store_packages', ['id' => $package->id, 'store_category_id' => null]);
});

test('module disabled denies access for a permissioned non superadmin', function () {
    config(['store.enabled' => false]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)
        ->get(route('admin.store.category.index'))
        ->assertStatus(403);
});

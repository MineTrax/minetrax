<?php

namespace Tests\Feature\Store;

use App\Models\StoreCategory;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ranks',
            'description' => 'Server ranks and perks.',
            'parent_id' => null,
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
        ], $overrides);
    }

    public function test_guest_cannot_access_admin_store_categories()
    {
        $this->get(route('admin.store-category.index'))->assertStatus(302);
    }

    public function test_non_staff_user_cannot_access_admin_store_categories()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.store-category.index'))->assertStatus(302);
        $this->get(route('admin.store-category.create'))->assertStatus(302);
    }

    public function test_admin_can_view_the_category_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCategory::factory()->count(3)->create();

        $this->get(route('admin.store-category.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Admin/StoreCategory/IndexStoreCategory', false));
    }

    public function test_admin_can_create_a_category()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store-category.store'), $this->validPayload())
            ->assertRedirect(route('admin.store-category.index'));

        $this->assertDatabaseHas('store_categories', [
            'name' => 'Ranks',
            'slug' => 'ranks',
            'is_enabled' => true,
        ]);
    }

    public function test_slug_is_derived_from_the_name()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store-category.store'), $this->validPayload(['name' => 'VIP  Ranks & Perks']));

        $this->assertDatabaseHas('store_categories', ['slug' => 'vip-ranks-perks']);
    }

    public function test_duplicate_slug_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCategory::factory()->create(['name' => 'Ranks', 'slug' => 'ranks']);

        $this->post(route('admin.store-category.store'), $this->validPayload())
            ->assertSessionHasErrors(['slug']);
    }

    public function test_name_is_required()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store-category.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_update_a_category()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();

        $this->put(route('admin.store-category.update', $category->id), $this->validPayload([
            'name' => 'Updated Name',
            'is_enabled' => false,
        ]))->assertRedirect(route('admin.store-category.index'));

        $category->refresh();
        $this->assertEquals('Updated Name', $category->name);
        $this->assertEquals('updated-name', $category->slug);
        $this->assertFalse($category->is_enabled);
    }

    public function test_updating_a_category_keeps_its_own_slug_available()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create(['name' => 'Ranks', 'slug' => 'ranks']);

        $this->put(route('admin.store-category.update', $category->id), $this->validPayload([
            'name' => 'Ranks',
        ]))->assertSessionHasNoErrors();
    }

    public function test_a_category_cannot_be_its_own_parent()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();

        $this->put(route('admin.store-category.update', $category->id), $this->validPayload([
            'parent_id' => $category->id,
        ]))->assertSessionHasErrors(['parent_id']);
    }

    public function test_admin_can_delete_a_category_without_destroying_its_packages()
    {
        $this->actingAs(User::whereId(1)->first());
        $category = StoreCategory::factory()->create();
        $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

        $this->delete(route('admin.store-category.delete', $category->id));

        $this->assertDatabaseMissing('store_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('store_packages', ['id' => $package->id, 'store_category_id' => null]);
    }

    public function test_module_disabled_denies_access_for_a_permissioned_non_superadmin()
    {
        config(['store.enabled' => false]);

        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)
            ->get(route('admin.store-category.index'))
            ->assertStatus(403);
    }
}

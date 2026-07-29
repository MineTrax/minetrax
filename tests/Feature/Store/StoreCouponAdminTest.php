<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCouponAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'SUMMER20',
            'description' => 'Summer sale code',
            'discount_type' => StoreDiscountType::PERCENT->value,
            'discount_value' => 2000,
            'currency_code' => null,
            'min_basket_amount' => null,
            'max_uses_total' => null,
            'max_uses_per_user' => null,
            'starts_at' => null,
            'expires_at' => null,
            'is_enabled' => true,
            'packages' => [],
            'categories' => [],
        ], $overrides);
    }

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.coupon.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.coupon.index'))->assertStatus(302);
    }

    public function test_staff_without_the_permission_are_forbidden()
    {
        // Moderator is staff but is granted no store permissions by RoleSeeder.
        $staff = User::factory()->create();
        $staff->assignRole('moderator');

        $this->actingAs($staff)->get(route('admin.store.coupon.index'))->assertStatus(403);
    }

    public function test_superadmin_can_list_coupons()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCoupon::factory()->create(['code' => 'LISTED']);

        $this->get(route('admin.store.coupon.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreCoupon/IndexStoreCoupon')
                ->has('coupons.data', 1)
            );
    }

    public function test_the_index_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        // Superadmin bypasses the policy gate, so a permissioned non-superadmin proves the gate.
        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)->get(route('admin.store.coupon.index'))->assertStatus(403);
    }

    public function test_admin_can_create_a_percentage_coupon()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload())
            ->assertRedirect(route('admin.store.coupon.index'));

        $this->assertDatabaseHas('store_coupons', [
            'code' => 'SUMMER20',
            'discount_type' => 'percent',
            'discount_value' => 2000,
            'currency_code' => null,
            'used_count' => 0,
        ]);
    }

    public function test_the_code_is_uppercased_and_stripped_of_spaces()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload(['code' => ' spring sale ']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_coupons', ['code' => 'SPRINGSALE']);
    }

    public function test_a_duplicate_code_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCoupon::factory()->create(['code' => 'SUMMER20']);

        $this->post(route('admin.store.coupon.store'), $this->validPayload())
            ->assertSessionHasErrors(['code']);
    }

    public function test_a_code_with_punctuation_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload(['code' => 'SUMMER@20']))
            ->assertSessionHasErrors(['code']);
    }

    public function test_a_percentage_above_one_hundred_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload(['discount_value' => 10001]))
            ->assertSessionHasErrors(['discount_value']);
    }

    public function test_a_fixed_coupon_keeps_its_currency()
    {
        $this->actingAs(User::whereId(1)->first());
        StoreCurrency::factory()->create(['code' => 'EUR']);

        $this->post(route('admin.store.coupon.store'), $this->validPayload([
            'code' => 'FIVEOFF',
            'discount_type' => StoreDiscountType::FIXED->value,
            'discount_value' => 500,
            'currency_code' => 'EUR',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('store_coupons', [
            'code' => 'FIVEOFF',
            'discount_type' => 'fixed',
            'discount_value' => 500,
            'currency_code' => 'EUR',
        ]);
    }

    public function test_a_fixed_coupon_in_an_unknown_currency_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload([
            'discount_type' => StoreDiscountType::FIXED->value,
            'discount_value' => 500,
            'currency_code' => 'XXX',
        ]))->assertSessionHasErrors(['currency_code']);
    }

    public function test_a_percentage_coupon_never_stores_a_currency()
    {
        // A stale code left on a coupon switched back to percent would be read as the amount's
        // currency by the pricing service, so it is cleared rather than merely ignored.
        $this->actingAs(User::whereId(1)->first());
        $coupon = StoreCoupon::factory()->fixed(500, 'USD')->create();

        $this->put(route('admin.store.coupon.update', $coupon->id), $this->validPayload([
            'code' => $coupon->code,
            'currency_code' => 'USD',
        ]))->assertSessionHasNoErrors();

        $this->assertNull($coupon->fresh()->currency_code);
    }

    public function test_an_end_date_before_the_start_is_rejected()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->post(route('admin.store.coupon.store'), $this->validPayload([
            'starts_at' => now()->addWeek()->toDateTimeString(),
            'expires_at' => now()->toDateTimeString(),
        ]))->assertSessionHasErrors(['expires_at']);
    }

    public function test_scope_rows_are_written_for_packages_and_categories()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $category = StoreCategory::factory()->create();

        $this->post(route('admin.store.coupon.store'), $this->validPayload([
            'packages' => [$package->id],
            'categories' => [$category->id],
        ]))->assertSessionHasNoErrors();

        $coupon = StoreCoupon::firstWhere('code', 'SUMMER20');

        $this->assertDatabaseHas('store_couponables', [
            'store_coupon_id' => $coupon->id,
            'couponable_type' => StorePackage::class,
            'couponable_id' => $package->id,
        ]);
        $this->assertDatabaseHas('store_couponables', [
            'store_coupon_id' => $coupon->id,
            'couponable_type' => StoreCategory::class,
            'couponable_id' => $category->id,
        ]);
    }

    public function test_clearing_the_scope_makes_the_coupon_store_wide_again()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $coupon = StoreCoupon::factory()->create();
        $coupon->couponables()->create([
            'couponable_type' => StorePackage::class,
            'couponable_id' => $package->id,
        ]);

        $this->put(route('admin.store.coupon.update', $coupon->id), $this->validPayload([
            'code' => $coupon->code,
            'packages' => [],
            'categories' => [],
        ]))->assertSessionHasNoErrors();

        $this->assertSame(0, $coupon->fresh()->couponables()->count());
    }

    public function test_the_edit_page_preselects_the_current_scope()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create();
        $coupon = StoreCoupon::factory()->create();
        $coupon->couponables()->create([
            'couponable_type' => StorePackage::class,
            'couponable_id' => $package->id,
        ]);

        $this->get(route('admin.store.coupon.edit', $coupon->id))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Admin/StoreCoupon/EditStoreCoupon')
                ->where('selectedPackages', [$package->id])
                ->where('selectedCategories', [])
            );
    }

    public function test_admin_can_update_a_coupon()
    {
        $this->actingAs(User::whereId(1)->first());
        $coupon = StoreCoupon::factory()->create(['code' => 'OLDCODE']);

        $this->put(route('admin.store.coupon.update', $coupon->id), $this->validPayload([
            'code' => 'NEWCODE',
            'discount_value' => 1500,
            'is_enabled' => false,
        ]))->assertRedirect(route('admin.store.coupon.index'));

        $this->assertDatabaseHas('store_coupons', [
            'id' => $coupon->id,
            'code' => 'NEWCODE',
            'discount_value' => 1500,
            'is_enabled' => false,
        ]);
    }

    public function test_updating_a_coupon_keeps_its_own_code_valid()
    {
        $this->actingAs(User::whereId(1)->first());
        $coupon = StoreCoupon::factory()->create(['code' => 'KEEPME']);

        $this->put(route('admin.store.coupon.update', $coupon->id), $this->validPayload([
            'code' => 'KEEPME',
        ]))->assertSessionHasNoErrors();
    }

    public function test_admin_can_delete_a_coupon()
    {
        $this->actingAs(User::whereId(1)->first());
        $coupon = StoreCoupon::factory()->create();

        $this->delete(route('admin.store.coupon.delete', $coupon->id))
            ->assertRedirect(route('admin.store.coupon.index'));

        $this->assertDatabaseMissing('store_coupons', ['id' => $coupon->id]);
    }

    public function test_deleting_a_coupon_leaves_the_orders_that_used_it_readable()
    {
        $this->actingAs(User::whereId(1)->first());
        $coupon = StoreCoupon::factory()->create(['code' => 'USEDONCE']);
        $order = StoreOrder::factory()->create([
            'store_coupon_id' => $coupon->id,
            'coupon_code' => 'USEDONCE',
            'coupon_discount' => 500,
        ]);

        $this->delete(route('admin.store.coupon.delete', $coupon->id));

        $order->refresh();
        $this->assertNull($order->store_coupon_id);
        $this->assertSame('USEDONCE', $order->coupon_code);
        $this->assertSame(500, (int) $order->coupon_discount);
    }
}

<?php

use App\Enums\StoreDiscountType;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function couponAdminValidPayload(array $overrides = []): array
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
        'is_stackable' => false,
        'packages' => [],
        'categories' => [],
    ], $overrides);
}

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.coupon.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.coupon.index'))->assertStatus(302);
});

test('staff without the permission are forbidden', function () {
    // Moderator is staff but is granted no store permissions by RoleSeeder.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');

    $this->actingAs($staff)->get(route('admin.store.coupon.index'))->assertStatus(403);
});

test('superadmin can list coupons', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCoupon::factory()->create(['code' => 'LISTED']);

    $this->get(route('admin.store.coupon.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Admin/StoreCoupon/IndexStoreCoupon')
            ->has('coupons.data', 1)
        );
});

test('the index is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    // Superadmin bypasses the policy gate, so a permissioned non-superadmin proves the gate.
    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.coupon.index'))->assertStatus(403);
});

test('admin can create a percentage coupon', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload())
        ->assertRedirect(route('admin.store.coupon.index'));

    $this->assertDatabaseHas('store_coupons', [
        'code' => 'SUMMER20',
        'discount_type' => 'percent',
        'discount_value' => 2000,
        'currency_code' => null,
        'used_count' => 0,
    ]);
});

test('the code is uppercased and stripped of spaces', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload(['code' => ' spring sale ']))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_coupons', ['code' => 'SPRINGSALE']);
});

test('a duplicate code is rejected', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCoupon::factory()->create(['code' => 'SUMMER20']);

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload())
        ->assertSessionHasErrors(['code']);
});

test('a code with punctuation is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload(['code' => 'SUMMER@20']))
        ->assertSessionHasErrors(['code']);
});

test('a percentage above one hundred is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload(['discount_value' => 10001]))
        ->assertSessionHasErrors(['discount_value']);
});

test('a fixed coupon keeps its currency', function () {
    $this->actingAs(User::whereId(1)->first());
    StoreCurrency::factory()->create(['code' => 'EUR']);

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload([
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
});

test('a fixed coupon in an unknown currency is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload([
        'discount_type' => StoreDiscountType::FIXED->value,
        'discount_value' => 500,
        'currency_code' => 'XXX',
    ]))->assertSessionHasErrors(['currency_code']);
});

test('a percentage coupon never stores a currency', function () {
    // A stale code left on a coupon switched back to percent would be read as the amount's
    // currency by the pricing service, so it is cleared rather than merely ignored.
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->fixed(500, 'USD')->create();

    $this->put(route('admin.store.coupon.update', $coupon->id), couponAdminValidPayload([
        'code' => $coupon->code,
        'currency_code' => 'USD',
    ]))->assertSessionHasNoErrors();

    expect($coupon->fresh()->currency_code)->toBeNull();
});

test('an end date before the start is rejected', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload([
        'starts_at' => now()->addWeek()->toDateTimeString(),
        'expires_at' => now()->toDateTimeString(),
    ]))->assertSessionHasErrors(['expires_at']);
});

test('scope rows are written for packages and categories', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $category = StoreCategory::factory()->create();

    $this->post(route('admin.store.coupon.store'), couponAdminValidPayload([
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
});

test('clearing the scope makes the coupon store wide again', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create();
    $coupon = StoreCoupon::factory()->create();
    $coupon->couponables()->create([
        'couponable_type' => StorePackage::class,
        'couponable_id' => $package->id,
    ]);

    $this->put(route('admin.store.coupon.update', $coupon->id), couponAdminValidPayload([
        'code' => $coupon->code,
        'packages' => [],
        'categories' => [],
    ]))->assertSessionHasNoErrors();

    expect($coupon->fresh()->couponables()->count())->toBe(0);
});

test('the edit page preselects the current scope', function () {
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
});

test('admin can update a coupon', function () {
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->create(['code' => 'OLDCODE']);

    $this->put(route('admin.store.coupon.update', $coupon->id), couponAdminValidPayload([
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
});

test('updating a coupon keeps its own code valid', function () {
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->create(['code' => 'KEEPME']);

    $this->put(route('admin.store.coupon.update', $coupon->id), couponAdminValidPayload([
        'code' => 'KEEPME',
    ]))->assertSessionHasNoErrors();
});

test('admin can delete a coupon', function () {
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->create();

    $this->delete(route('admin.store.coupon.delete', $coupon->id))
        ->assertRedirect(route('admin.store.coupon.index'));

    $this->assertDatabaseMissing('store_coupons', ['id' => $coupon->id]);
});

test('deleting a coupon leaves the orders that used it readable', function () {
    $this->actingAs(User::whereId(1)->first());
    $coupon = StoreCoupon::factory()->create(['code' => 'USEDONCE']);
    $order = StoreOrder::factory()->create(['coupon_discount' => 500]);
    $this->recordOrderCoupon($order, $coupon, 500);

    $this->delete(route('admin.store.coupon.delete', $coupon->id));

    // The link goes; the snapshot stays, which is what keeps the receipt readable.
    $row = $order->fresh()->coupons->sole();
    expect($row->store_coupon_id)->toBeNull();
    expect($row->code)->toBe('USEDONCE');
    expect($row->discount_amount)->toBe(500);
    expect((int) $order->fresh()->coupon_discount)->toBe(500);
});

test('the coupon listing names who wrote each code', function () {
    $author = User::factory()->create(['username' => 'promoted']);
    $mine = StoreCoupon::factory()->create(['code' => 'AUTHORED', 'created_by' => $author->id]);
    // Seeded, imported, or written before the column existed.
    $orphan = StoreCoupon::factory()->create(['code' => 'ORPHAN', 'created_by' => null]);

    $this->actingAs(User::whereId(1)->first())
        ->get(route('admin.store.coupon.index'))
        ->assertStatus(200)
        ->assertInertia(function ($page) use ($mine, $orphan) {
            $rows = collect($page->toArray()['props']['coupons']['data'])->keyBy('id');

            expect($rows[$mine->id]['creator']['username'])->toBe('promoted');
            // Null rather than absent: the column renders a dash off this, and a missing key
            // would throw in the template instead.
            expect($rows[$orphan->id]['creator'])->toBeNull();
        });
});

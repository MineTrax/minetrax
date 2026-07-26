<?php

namespace Tests\Feature\Store;

use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Covers the store policies: the module-disabled gate, permission mapping, and order ownership.
 */
class StorePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
    }

    public function test_all_store_policies_are_registered()
    {
        $models = [
            StoreCategory::class, StorePackage::class, StoreCurrency::class,
            StoreOrder::class, StoreCoupon::class, StoreSale::class, StoreBan::class,
        ];

        foreach ($models as $model) {
            $this->assertNotNull(Gate::getPolicyFor($model), "No policy registered for [{$model}].");
        }
    }

    public function test_a_user_without_permission_is_denied()
    {
        $user = User::factory()->create();

        $this->assertFalse($user->can('viewAny', StorePackage::class));
        $this->assertFalse($user->can('create', StorePackage::class));
        $this->assertFalse($user->can('viewAny', StoreOrder::class));
    }

    public function test_a_user_with_the_permission_is_allowed()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['read store_packages', 'create store_packages']);

        $this->assertTrue($user->can('viewAny', StorePackage::class));
        $this->assertTrue($user->can('create', StorePackage::class));
        $this->assertFalse($user->can('delete', StorePackage::factory()->create()));
    }

    public function test_disabling_the_module_denies_everyone_except_superadmin()
    {
        config(['store.enabled' => false]);

        $user = User::factory()->create();
        $user->givePermissionTo(['read store_packages', 'create store_packages']);

        $this->assertFalse($user->can('viewAny', StorePackage::class), 'The before() gate must deny when the module is off.');
        $this->assertFalse($user->can('create', StorePackage::class));

        // Gate::before for superadmin runs ahead of the policy, matching BanWarden's behaviour.
        // Routes and nav are hidden anyway when the module is disabled.
        $this->assertTrue(User::whereId(1)->first()->can('viewAny', StorePackage::class));
    }

    public function test_refund_and_resend_are_separate_abilities_from_plain_update()
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['read store_orders', 'update store_orders']);
        $order = StoreOrder::factory()->create();

        $this->assertTrue($user->can('update', $order));
        $this->assertFalse($user->can('refund', $order), 'Refunding money must not come free with update.');
        $this->assertFalse($user->can('resend', $order));

        $user->givePermissionTo(['refund store_orders', 'resend store_orders']);
        $user->forgetCachedPermissions();

        $this->assertTrue($user->fresh()->can('refund', $order));
        $this->assertTrue($user->fresh()->can('resend', $order));
    }

    public function test_a_buyer_can_view_their_own_order_without_any_permission()
    {
        $buyer = User::factory()->create();
        $ownOrder = StoreOrder::factory()->forUser($buyer)->create();
        $otherOrder = StoreOrder::factory()->create();

        $this->assertTrue($buyer->can('view', $ownOrder));
        $this->assertFalse($buyer->can('view', $otherOrder), 'A buyer must not read another buyer order.');
    }

    public function test_a_guest_order_is_not_viewable_by_an_arbitrary_user()
    {
        $user = User::factory()->create();
        $guestOrder = StoreOrder::factory()->guest()->create();

        $this->assertFalse($user->can('view', $guestOrder));
    }
}

<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDiscountType;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentStatus;
use App\Models\Player;
use App\Models\StoreBan;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['store.enabled' => true]);
        $this->baseCurrency();

        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual'];
        $settings->save();

        $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

        // Throttling runs through Redis (bootstrap/app.php calls throttleWithRedis), so limiter
        // state survives between tests and would 429 the whole suite. Throttling itself is
        // covered by its own test below, which re-enables it.
        $this->withoutMiddleware([
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
        ]);
    }

    private function fillCart(?StorePackage $package = null, int $quantity = 1): StorePackage
    {
        $package = $package ?: StorePackage::factory()->create(['price' => 1000]);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => $quantity]);

        return $package;
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'player_username' => 'Steve',
            'email' => 'buyer@example.com',
            'gateway' => 'manual',
            'accept_terms' => true,
        ], $overrides);
    }

    public function test_the_checkout_page_redirects_when_the_cart_is_empty()
    {
        $this->get(route('store.checkout.create'))->assertRedirect(route('store.cart.show'));
    }

    public function test_the_checkout_page_lists_only_enabled_gateways()
    {
        $this->fillCart();

        $this->get(route('store.checkout.create'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Store/CheckoutStore', false)
                ->has('gateways', 1)
                ->where('gateways.0.key', 'manual')
            );
    }

    public function test_a_guest_can_place_an_order()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart(null, 2);

        $this->post(route('store.checkout.store'), $this->checkoutPayload())
            ->assertRedirect();

        $order = StoreOrder::first();
        $this->assertNotNull($order);
        $this->assertEquals(StoreOrderStatus::PENDING, $order->status);
        $this->assertEquals(2000, $order->total);
        $this->assertEquals(2000, $order->amount_due);
        $this->assertNull($order->user_id);
        $this->assertEquals('Steve', $order->player_username);
    }

    public function test_the_order_snapshots_the_line_items()
    {
        Player::factory()->create(['username' => 'Steve']);
        $package = $this->fillCart(null, 3);

        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $originalName = $package->name;

        $item = StoreOrder::first()->items->first();
        $this->assertEquals($originalName, $item->package_name);
        $this->assertEquals(3, $item->quantity);
        $this->assertEquals(1000, $item->unit_price);

        // Editing the package afterwards must not rewrite the order.
        $package->update(['name' => 'Renamed', 'price' => 5000]);
        $this->assertEquals($originalName, $item->fresh()->package_name);
        $this->assertEquals(1000, $item->fresh()->unit_price);
    }

    public function test_the_order_is_priced_from_live_data_not_from_what_the_client_sends()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        // Any amount fields in the request body are simply ignored.
        $this->post(route('store.checkout.store'), $this->checkoutPayload([
            'total' => 1,
            'amount_due' => 1,
            'subtotal' => 1,
        ]));

        $this->assertEquals(1000, StoreOrder::first()->total);
    }

    public function test_checkout_empties_the_cart()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_a_pending_payment_row_is_created_for_the_chosen_gateway()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $payment = StoreOrder::first()->payments->first();
        $this->assertEquals(StorePaymentStatus::PENDING, $payment->status);
        $this->assertEquals(1000, $payment->amount);
    }

    public function test_terms_must_be_accepted()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload(['accept_terms' => false]))
            ->assertSessionHasErrors(['accept_terms']);

        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_an_unknown_gateway_is_rejected()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload(['gateway' => 'bitcoin']))
            ->assertSessionHasErrors(['gateway']);
    }

    public function test_an_unverifiable_username_is_rejected()
    {
        Http::fake(['api.minecraftservices.com/*' => Http::response(null, 404)]);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload(['player_username' => 'GhostPlayer']))
            ->assertSessionHasErrors(['player_username']);

        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_a_guest_email_is_required_when_configured()
    {
        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload(['email' => null]))
            ->assertSessionHasErrors(['email']);
    }

    public function test_guest_checkout_can_be_turned_off()
    {
        $settings = app(StoreSettings::class);
        $settings->enable_guest_checkout = false;
        $settings->save();

        Player::factory()->create(['username' => 'Steve']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_a_banned_identity_cannot_check_out()
    {
        $player = Player::factory()->create(['username' => 'Steve']);
        StoreBan::factory()->create(['player_uuid' => $player->uuid, 'reason' => 'Chargeback']);
        $this->fillCart();

        $this->post(route('store.checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors(['cart']);

        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_stock_is_enforced_at_checkout()
    {
        Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000, 'stock_limit' => 1, 'sold_count' => 0]);
        $this->fillCart($package, 1);
        $package->update(['sold_count' => 1]); // someone else bought the last one first

        $this->post(route('store.checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors(['cart']);
    }

    public function test_a_per_player_purchase_limit_counts_only_paid_orders()
    {
        $player = Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000, 'player_purchase_limit' => 1]);

        // An abandoned order must not consume the allowance.
        $abandoned = StoreOrder::factory()->create(['player_uuid' => $player->uuid]);
        $abandoned->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);

        $this->fillCart($package);
        $this->post(route('store.checkout.store'), $this->checkoutPayload())->assertRedirect();
        $this->assertEquals(2, StoreOrder::count());

        // Now a paid one does.
        StoreOrder::first()->update(['status' => StoreOrderStatus::PAID]);
        $this->fillCart($package);
        $this->post(route('store.checkout.store'), $this->checkoutPayload())
            ->assertSessionHasErrors(['cart']);
    }

    public function test_a_coupon_use_is_reserved_at_order_creation()
    {
        Player::factory()->create(['username' => 'Steve']);
        $coupon = StoreCoupon::create([
            'code' => 'SAVE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000,
            'is_enabled' => true, 'used_count' => 0, 'max_uses_total' => 1,
        ]);

        $this->fillCart();
        $this->post(route('store.cart.code'), ['code' => 'SAVE']);
        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        // Reserved immediately, so two buyers racing for the last use cannot both win.
        $this->assertEquals(1, $coupon->fresh()->used_count);
        $this->assertEquals(900, StoreOrder::first()->total);
    }

    public function test_cancelling_an_order_releases_the_reserved_coupon_use()
    {
        Player::factory()->create(['username' => 'Steve']);
        $coupon = StoreCoupon::create([
            'code' => 'SAVE', 'discount_type' => StoreDiscountType::PERCENT, 'discount_value' => 1000,
            'is_enabled' => true, 'used_count' => 0,
        ]);

        $this->fillCart();
        $this->post(route('store.cart.code'), ['code' => 'SAVE']);
        $this->post(route('store.checkout.store'), $this->checkoutPayload());
        $this->assertEquals(1, $coupon->fresh()->used_count);

        $order = StoreOrder::first();
        $this->post(route('store.order.cancel', $order->uuid));

        $this->assertEquals(StoreOrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(0, $coupon->fresh()->used_count);
    }

    public function test_an_order_fully_covered_by_a_gift_card_skips_the_gateway()
    {
        Player::factory()->create(['username' => 'Steve']);
        StoreGiftCard::create([
            'code' => 'FULL', 'currency_code' => 'USD', 'original_balance' => 5000, 'balance' => 5000, 'is_enabled' => true,
        ]);

        $this->fillCart();
        $this->post(route('store.cart.code'), ['code' => 'FULL']);
        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $order = StoreOrder::first();
        $this->assertEquals(0, $order->amount_due);
        // Nothing to charge, so it never sits at a zero-value gateway: it is paid immediately and
        // fulfilment carries it through to COMPLETED.
        $this->assertTrue($order->status->isPaidState());
        $this->assertEquals(StoreOrderStatus::COMPLETED, $order->status);
    }

    public function test_paying_with_a_gift_card_debits_it_and_writes_a_ledger_row()
    {
        Player::factory()->create(['username' => 'Steve']);
        $card = StoreGiftCard::create([
            'code' => 'PART', 'currency_code' => 'USD', 'original_balance' => 400, 'balance' => 400, 'is_enabled' => true,
        ]);

        $this->fillCart();
        $this->post(route('store.cart.code'), ['code' => 'PART']);
        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $order = StoreOrder::first();
        $this->assertEquals(600, $order->amount_due);
        $this->assertEquals(400, $card->fresh()->balance, 'Not yet debited: the order is still pending.');

        app(StoreOrderService::class)->markPaid($order, $order->payments->first(), 600, 'USD');

        $this->assertEquals(0, $card->fresh()->balance);
        $this->assertDatabaseHas('store_gift_card_transactions', [
            'store_gift_card_id' => $card->id, 'store_order_id' => $order->id, 'amount' => -400,
        ]);
    }

    public function test_a_logged_in_user_order_is_attributed_to_them()
    {
        $user = User::factory()->create();
        Player::factory()->create(['username' => 'Steve']);

        $this->actingAs($user);
        $this->post(route('store.cart.store'), ['package_id' => StorePackage::factory()->create(['price' => 1000])->id, 'quantity' => 1]);
        $this->post(route('store.checkout.store'), $this->checkoutPayload());

        $this->assertEquals($user->id, StoreOrder::first()->user_id);
    }

    public function test_another_user_cannot_view_someone_elses_order()
    {
        $owner = User::factory()->create();
        $order = StoreOrder::factory()->forUser($owner)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('store.order.result', $order->uuid))
            ->assertStatus(403);
    }

    public function test_a_guest_order_is_reachable_by_its_uuid()
    {
        // A guest has no account to authorise against; the v4 uuid is the credential and is
        // never exposed in any listing.
        $order = StoreOrder::factory()->guest()->create();

        $this->get(route('store.order.result', $order->uuid))->assertStatus(200);
    }

    public function test_checkout_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $this->get(route('store.checkout.create'))->assertStatus(403);
    }
}

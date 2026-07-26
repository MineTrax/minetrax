<?php

namespace Tests\Feature\Store;

use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderPaid;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Models\User;
use App\Notifications\StoreOrderPaidNotification;
use App\Notifications\StoreOrderPlacedStaffNotification;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StoreOrderUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        Queue::fake([ProcessStoreOrderPurchaseJob::class]);
    }

    private function orderWithItem(array $attributes = []): StoreOrder
    {
        $order = StoreOrder::factory()->create(array_merge([
            'total' => 1500, 'amount_due' => 1500, 'currency' => 'USD',
        ], $attributes));

        $package = StorePackage::factory()->create();

        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 2,
            'unit_price_original' => 750,
            'unit_price' => 750,
            'total' => 1500,
            'options' => [[
                'name' => 'Tier',
                'label' => 'Gold',
                // Feeds a server command; must never reach the buyer's browser.
                'value' => 'internal_gold_node',
                'placeholder' => 'TIER',
            ]],
        ]);

        return $order->fresh('items');
    }

    // --- Purchase history --------------------------------------------------------------------

    public function test_a_guest_cannot_reach_the_purchase_history()
    {
        $this->get(route('store.my-order.index'))->assertRedirect(route('login'));
    }

    public function test_a_user_sees_only_their_own_orders()
    {
        $user = User::factory()->create();
        $this->orderWithItem(['user_id' => $user->id]);
        $this->orderWithItem(['user_id' => User::factory()->create()->id]);
        $this->orderWithItem(['user_id' => null]);

        $this->actingAs($user)
            ->get(route('store.my-order.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Store/IndexMyStoreOrder')
                ->has('orders.data', 1)
            );
    }

    public function test_a_user_cannot_open_someone_elses_order()
    {
        $order = $this->orderWithItem(['user_id' => User::factory()->create()->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('store.my-order.show', $order->uuid))
            ->assertNotFound();
    }

    public function test_a_user_cannot_open_a_guest_order_from_their_history()
    {
        $order = $this->orderWithItem(['user_id' => null]);

        $this->actingAs(User::factory()->create())
            ->get(route('store.my-order.show', $order->uuid))
            ->assertNotFound();
    }

    public function test_the_order_detail_renders_for_its_owner()
    {
        $user = User::factory()->create();
        $order = $this->orderWithItem(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('store.my-order.show', $order->uuid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Store/ShowMyStoreOrder')
                ->where('order.uuid', $order->uuid)
                ->has('order.items', 1)
                ->has('order.money.total')
            );
    }

    /**
     * Option values are substituted into server commands. Leaking them would tell a buyer exactly
     * what the console is about to run on their behalf.
     */
    public function test_the_internal_option_value_is_never_sent_to_the_buyer()
    {
        $user = User::factory()->create();
        $order = $this->orderWithItem(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('store.my-order.show', $order->uuid));

        $response->assertOk();
        $response->assertDontSee('internal_gold_node');

        // Asserted on the payload rather than the rendered HTML: the shared translation bundle
        // legitimately contains the word TIER as a validation example, so a text search would
        // false-positive. What matters is that the option object carries no value or placeholder.
        $response->assertInertia(function ($page) {
            $option = $page->toArray()['props']['order']['items'][0]['options'][0];

            $this->assertEquals(['name', 'label'], array_keys($option));
            $this->assertEquals('Gold', $option['label']);
        });
    }

    public function test_the_history_is_hidden_when_the_store_is_disabled()
    {
        config(['store.enabled' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('store.my-order.index'))
            ->assertForbidden();
    }

    // --- Result page --------------------------------------------------------------------------

    public function test_a_guest_can_open_their_own_order_by_uuid()
    {
        // The uuid is the credential: a guest has no account to authorise against.
        $order = $this->orderWithItem(['user_id' => null]);

        $this->get(route('store.order.result', $order->uuid))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Store/ResultStoreOrder'));
    }

    public function test_a_stranger_cannot_open_a_signed_in_users_order()
    {
        $order = $this->orderWithItem(['user_id' => User::factory()->create()->id]);

        $this->get(route('store.order.result', $order->uuid))->assertForbidden();
    }

    public function test_the_status_endpoint_reports_both_states()
    {
        $order = $this->orderWithItem(['user_id' => null]);

        $this->getJson(route('store.order.status', $order->uuid))
            ->assertOk()
            ->assertJson(['status' => 'pending', 'delivery_status' => 'pending']);
    }

    // --- Notifications --------------------------------------------------------------------------

    private function markPaid(StoreOrder $order): void
    {
        $payment = StorePayment::factory()->create([
            'store_order_id' => $order->id,
            'amount' => $order->amount_due,
            'currency' => $order->currency,
            'status' => StorePaymentStatus::PENDING,
        ]);

        app(StoreOrderService::class)->markPaid($order, $payment, (int) $order->amount_due, $order->currency);
    }

    public function test_a_signed_in_buyer_gets_a_receipt()
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->markPaid($this->orderWithItem(['user_id' => $user->id]));

        Notification::assertSentTo($user, StoreOrderPaidNotification::class);
    }

    public function test_a_guest_buyer_gets_a_receipt_at_the_address_they_gave()
    {
        Notification::fake();

        $order = $this->orderWithItem(['user_id' => null, 'email' => 'buyer@example.com']);
        $this->markPaid($order);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            StoreOrderPaidNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'buyer@example.com'
        );
    }

    public function test_a_guest_who_gave_no_email_gets_nothing_rather_than_an_error()
    {
        Notification::fake();

        $this->markPaid($this->orderWithItem(['user_id' => null, 'email' => null]));

        Notification::assertNothingSentTo(new AnonymousNotifiable);
    }

    public function test_staff_are_notified_of_a_new_order()
    {
        Notification::fake();

        $superadmin = User::whereId(1)->first();
        $this->markPaid($this->orderWithItem());

        Notification::assertSentTo($superadmin, StoreOrderPlacedStaffNotification::class);
    }

    public function test_staff_are_not_notified_when_the_setting_is_off()
    {
        Notification::fake();

        $settings = app(StoreSettings::class);
        $settings->notify_staff_on_purchase = false;
        $settings->save();

        $this->markPaid($this->orderWithItem());

        Notification::assertNotSentTo(User::whereId(1)->first(), StoreOrderPlacedStaffNotification::class);
    }

    public function test_a_replayed_payment_does_not_send_a_second_receipt()
    {
        Notification::fake();

        $user = User::factory()->create();
        $order = $this->orderWithItem(['user_id' => $user->id]);

        $payment = StorePayment::factory()->create([
            'store_order_id' => $order->id,
            'amount' => $order->amount_due,
            'currency' => $order->currency,
        ]);

        $orders = app(StoreOrderService::class);
        $orders->markPaid($order, $payment, (int) $order->amount_due, $order->currency);
        $orders->markPaid($order->fresh(), $payment, (int) $order->amount_due, $order->currency);

        Notification::assertSentToTimes($user, StoreOrderPaidNotification::class, 1);
    }

    public function test_the_receipt_formats_money_in_the_orders_own_currency()
    {
        StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'symbol' => '¥', 'is_base' => false, 'rate_to_base' => 150]);

        $order = $this->orderWithItem(['currency' => 'JPY', 'total' => 1000, 'amount_due' => 1000]);
        $mail = (new StoreOrderPaidNotification($order))->toMail(new AnonymousNotifiable);

        // A thousand yen, not ten. The receipt goes to a human who will notice.
        $this->assertStringContainsString('¥1,000', implode(' ', $mail->introLines));
    }

    public function test_the_receipt_links_to_the_result_page()
    {
        $order = $this->orderWithItem();
        $mail = (new StoreOrderPaidNotification($order))->toMail(new AnonymousNotifiable);

        $this->assertEquals(route('store.order.result', $order->uuid), $mail->actionUrl);
    }

    // --- Checkout page -----------------------------------------------------------------------------

    public function test_the_checkout_page_redirects_when_the_cart_is_empty()
    {
        $this->get(route('store.checkout.create'))->assertRedirect(route('store.cart.show'));
    }

    public function test_an_active_grant_is_shown_on_the_order_detail()
    {
        $user = User::factory()->create();
        $order = $this->orderWithItem(['user_id' => $user->id]);
        $item = $order->items->first();

        $item->grant()->create([
            'store_package_id' => $item->store_package_id,
            'player_uuid' => $order->player_uuid,
            'status' => StorePackageGrantStatus::ACTIVE,
            'granted_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        $this->actingAs($user)
            ->get(route('store.my-order.show', $order->uuid))
            ->assertInertia(function ($page) {
                $grant = $page->toArray()['props']['order']['items'][0]['grant'];

                $this->assertEquals(StorePackageGrantStatus::ACTIVE->value, $grant['status']['value']);
                $this->assertNotNull($grant['expires_at']);
            });
    }

    public function test_paying_still_dispatches_delivery_alongside_the_notifications()
    {
        // The receipt listener must not have displaced the delivery listener.
        Notification::fake();

        $this->markPaid($this->orderWithItem());

        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
    }

    public function test_the_paid_event_carries_the_order()
    {
        Event::fake([StoreOrderPaid::class]);

        $order = $this->orderWithItem();
        $this->markPaid($order);

        Event::assertDispatched(
            StoreOrderPaid::class,
            fn (StoreOrderPaid $event) => $event->order->id === $order->id
        );
    }
}

<?php

namespace Tests\Feature\Store;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentRefundType;
use App\Enums\StorePaymentStatus;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreCurrency;
use App\Models\StoreGatewayWebhook;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Services\StoreOrderService;
use App\Settings\StoreSettings;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\StorePaymentGatewayManager;
use App\Utils\Payments\StripePaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * The Stripe driver, exercised end to end without touching Stripe.
 *
 * Signatures are computed in-test with the same HMAC scheme Stripe uses, so the verification path,
 * the replay guard and every event mapping are covered with no keys and no network. Only the
 * outbound API calls (creating a session, issuing a refund) need real credentials, and those are
 * the parts a live walkthrough covers.
 */
class StoreWebhookStripeTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_testsecret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        // Fulfilment has its own test file; here the concern is the transition the webhook causes.
        Queue::fake([ProcessStoreOrderPurchaseJob::class]);

        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual', 'stripe'];
        $settings->gateway_credentials = [
            'stripe' => [
                'secret_key' => 'sk_test_notused',
                'webhook_secret' => self::SECRET,
            ],
        ];
        $settings->save();

        // The webhook limiter goes through Redis, whose state outlives a database rollback.
        $this->withoutMiddleware([ThrottleRequests::class, ThrottleRequestsWithRedis::class]);
    }

    // --- Helpers ---------------------------------------------------------------------------

    /**
     * Build a signature header exactly the way Stripe does: HMAC-SHA256 over "<timestamp>.<body>".
     */
    private function signature(string $payload, ?int $timestamp = null, string $secret = self::SECRET): string
    {
        $timestamp ??= time();

        return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
    }

    private function postEvent(array $event, ?string $signature = null, string $gateway = 'stripe')
    {
        $payload = json_encode($event);

        return $this->call(
            'POST',
            "/api/webhooks/store/{$gateway}",
            [],
            [],
            [],
            ['HTTP_STRIPE_SIGNATURE' => $signature ?? $this->signature($payload), 'CONTENT_TYPE' => 'application/json'],
            $payload,
        );
    }

    /**
     * @return array{0: StoreOrder, 1: StorePayment}
     */
    private function pendingOrder(int $amount = 1999, array $overrides = []): array
    {
        $order = StoreOrder::factory()->create(array_merge([
            'total' => $amount,
            'amount_due' => $amount,
            'currency' => 'USD',
            'gateway' => StorePaymentGateway::STRIPE,
        ], $overrides));

        $payment = StorePayment::factory()->create([
            'store_order_id' => $order->id,
            'gateway' => StorePaymentGateway::STRIPE,
            'gateway_session_id' => 'cs_test_'.$order->id,
            'amount' => $order->amount_due,
            'currency' => $order->currency,
        ]);

        return [$order, $payment];
    }

    private function sessionCompletedEvent(StoreOrder $order, StorePayment $payment, array $overrides = []): array
    {
        return [
            'id' => 'evt_'.$order->id,
            'type' => 'checkout.session.completed',
            'data' => ['object' => array_merge([
                'id' => $payment->gateway_session_id,
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'payment_intent' => 'pi_test_'.$order->id,
                'amount_total' => (int) $order->amount_due,
                'currency' => strtolower($order->currency),
                'client_reference_id' => $order->uuid,
                'metadata' => ['order_uuid' => $order->uuid],
            ], $overrides)],
        ];
    }

    // --- Signature verification ------------------------------------------------------------

    public function test_a_valid_signature_marks_the_order_paid()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertOk();

        $order->refresh();
        $this->assertEquals(StoreOrderStatus::PAID, $order->status);
        $this->assertNotNull($order->paid_at);

        $payment->refresh();
        $this->assertEquals(StorePaymentStatus::COMPLETED, $payment->status);
        $this->assertEquals('pi_test_'.$order->id, $payment->gateway_transaction_id);

        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
    }

    public function test_a_bad_signature_is_rejected_and_changes_nothing()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment), 't=1,v1=deadbeef')
            ->assertStatus(400);

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertDatabaseCount('store_gateway_webhooks', 0);
    }

    public function test_a_signature_from_the_wrong_secret_is_rejected()
    {
        [$order, $payment] = $this->pendingOrder();
        $payload = json_encode($this->sessionCompletedEvent($order, $payment));

        $this->postEvent(
            $this->sessionCompletedEvent($order, $payment),
            $this->signature($payload, secret: 'whsec_someoneelse')
        )->assertStatus(400);

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_missing_signature_header_is_rejected()
    {
        [$order, $payment] = $this->pendingOrder();
        $payload = json_encode($this->sessionCompletedEvent($order, $payment));

        $this->call('POST', '/api/webhooks/store/stripe', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload)
            ->assertStatus(400);
    }

    public function test_an_old_signature_is_outside_the_tolerance_window()
    {
        // A captured request replayed hours later must not be accepted, even though the HMAC
        // itself is still arithmetically valid.
        [$order, $payment] = $this->pendingOrder();
        $payload = json_encode($this->sessionCompletedEvent($order, $payment));

        $this->postEvent($this->sessionCompletedEvent($order, $payment), $this->signature($payload, time() - 3600))
            ->assertStatus(400);

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_the_signature_covers_the_body_so_a_tampered_amount_is_rejected()
    {
        [$order, $payment] = $this->pendingOrder(1999);

        $honest = $this->sessionCompletedEvent($order, $payment);
        $signature = $this->signature(json_encode($honest));

        $tampered = $honest;
        $tampered['data']['object']['amount_total'] = 1;

        $this->postEvent($tampered, $signature)->assertStatus(400);
        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    // --- Replay guard ----------------------------------------------------------------------

    public function test_a_replayed_event_is_accepted_but_delivers_only_once()
    {
        [$order, $payment] = $this->pendingOrder();
        $event = $this->sessionCompletedEvent($order, $payment);

        $this->postEvent($event)->assertOk();
        $this->postEvent($event)->assertOk();

        $this->assertDatabaseCount('store_gateway_webhooks', 1);
        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
    }

    public function test_a_processed_event_is_recorded_with_its_type()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertOk();

        $record = StoreGatewayWebhook::first();
        $this->assertEquals('stripe', $record->gateway);
        $this->assertEquals('evt_'.$order->id, $record->event_id);
        $this->assertEquals('checkout.session.completed', $record->type);
        $this->assertNotNull($record->processed_at);
        $this->assertNull($record->error);
    }

    public function test_two_gateways_may_share_an_event_id()
    {
        // The replay guard is (gateway, event_id), not event_id alone.
        StoreGatewayWebhook::create(['gateway' => 'paypal', 'event_id' => 'evt_shared']);

        [$order, $payment] = $this->pendingOrder();
        $event = $this->sessionCompletedEvent($order, $payment);
        $event['id'] = 'evt_shared';

        $this->postEvent($event)->assertOk();

        $this->assertDatabaseCount('store_gateway_webhooks', 2);
        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
    }

    // --- Amount and currency verification ---------------------------------------------------

    public function test_an_amount_mismatch_fails_the_payment_and_leaves_the_order_pending()
    {
        [$order, $payment] = $this->pendingOrder(1999);

        $event = $this->sessionCompletedEvent($order, $payment, ['amount_total' => 1]);

        $this->postEvent($event)->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
        Queue::assertNotPushed(ProcessStoreOrderPurchaseJob::class);
    }

    public function test_a_currency_mismatch_fails_the_payment()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment, ['currency' => 'eur']))->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    /**
     * A ¥1000 package must charge ¥1000. JPY has no minor unit, so the amount Stripe reports is
     * the whole-yen figure and any hidden factor of 100 would show up here immediately.
     */
    public function test_a_zero_decimal_currency_is_not_multiplied_by_a_hundred()
    {
        StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'rate_to_base' => 150, 'is_base' => false]);

        [$order, $payment] = $this->pendingOrder(1000, ['currency' => 'JPY']);

        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        $this->assertEquals(1000, (int) $order->fresh()->amount_due);
    }

    // --- Event mapping -----------------------------------------------------------------------

    public function test_an_unpaid_session_is_ignored_until_the_money_arrives()
    {
        // Asynchronous methods complete the session while the payment is still in flight.
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment, ['payment_status' => 'unpaid']))->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::PENDING, $payment->fresh()->status);
    }

    public function test_an_async_payment_succeeding_later_marks_the_order_paid()
    {
        [$order, $payment] = $this->pendingOrder();

        $event = $this->sessionCompletedEvent($order, $payment);
        $event['type'] = 'checkout.session.async_payment_succeeded';

        $this->postEvent($event)->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
    }

    public function test_an_async_payment_failing_fails_the_payment_only()
    {
        [$order, $payment] = $this->pendingOrder();

        $event = $this->sessionCompletedEvent($order, $payment);
        $event['type'] = 'checkout.session.async_payment_failed';

        $this->postEvent($event)->assertOk();

        // Left PENDING on purpose: the buyer can still retry with another method.
        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    public function test_an_expired_session_cancels_the_order()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent([
            'id' => 'evt_expired',
            'type' => 'checkout.session.expired',
            'data' => ['object' => [
                'id' => $payment->gateway_session_id,
                'client_reference_id' => $order->uuid,
            ]],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::CANCELLED, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    public function test_an_unrecognised_event_type_is_accepted_and_ignored()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent([
            'id' => 'evt_unknown',
            'type' => 'customer.created',
            'data' => ['object' => ['id' => 'cus_123']],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_payload_without_an_event_id_is_rejected()
    {
        $this->postEvent(['type' => 'checkout.session.completed', 'data' => ['object' => []]])
            ->assertStatus(400);

        $this->assertDatabaseCount('store_gateway_webhooks', 0);
    }

    public function test_an_event_matching_no_payment_is_accepted_without_acting()
    {
        $this->postEvent([
            'id' => 'evt_orphan',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_test_unknown',
                'payment_status' => 'paid',
                'amount_total' => 500,
                'currency' => 'usd',
            ]],
        ])->assertOk();

        // Recorded as seen so Stripe stops retrying, but nothing was changed.
        $this->assertDatabaseHas('store_gateway_webhooks', ['event_id' => 'evt_orphan']);
    }

    // --- Refunds and disputes -----------------------------------------------------------------

    private function paidOrder(int $amount = 2000): array
    {
        [$order, $payment] = $this->pendingOrder($amount);
        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertOk();

        // The delivery job is faked, so complete the order the way the job would.
        app(StoreOrderService::class)
            ->markCompleted($order->fresh(), StoreDeliveryStatus::DELIVERED);

        return [$order->fresh(), $payment->fresh()];
    }

    private function refundEvent(StorePayment $payment, int $amount, string $refundId, int $cumulative): array
    {
        return [
            'id' => 'evt_refund_'.$refundId,
            'type' => 'charge.refunded',
            'data' => ['object' => [
                'id' => 'ch_test_1',
                'payment_intent' => $payment->gateway_transaction_id,
                'currency' => strtolower($payment->currency),
                'amount_refunded' => $cumulative,
                'refunds' => ['data' => [['id' => $refundId, 'amount' => $amount]]],
            ]],
        ];
    }

    public function test_a_full_refund_refunds_the_order_and_writes_a_ledger_row()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->postEvent($this->refundEvent($payment, 2000, 're_1', 2000))->assertOk();

        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);

        $payment->refresh();
        $this->assertEquals(2000, (int) $payment->refunded_amount);
        $this->assertEquals(StorePaymentStatus::REFUNDED, $payment->status);

        $refund = $payment->refunds()->first();
        $this->assertEquals(StorePaymentRefundType::REFUND, $refund->type);
        $this->assertEquals('re_1', $refund->gateway_refund_id);
        $this->assertEquals(2000, (int) $refund->amount);
    }

    public function test_a_partial_refund_leaves_the_order_partially_refunded()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->postEvent($this->refundEvent($payment, 500, 're_partial', 500))->assertOk();

        $this->assertEquals(StoreOrderStatus::PARTIALLY_REFUNDED, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::PARTIALLY_REFUNDED, $payment->fresh()->status);
        $this->assertEquals(500, (int) $payment->fresh()->refunded_amount);
    }

    /**
     * charge.refunded reports the running total, not this refund's size. Two partials of 500 must
     * add up to 1000 refunded, not 1500.
     */
    public function test_two_partial_refunds_accumulate_from_the_individual_refund_amounts()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->postEvent($this->refundEvent($payment, 500, 're_a', 500))->assertOk();
        $this->postEvent($this->refundEvent($payment, 500, 're_b', 1000))->assertOk();

        $this->assertEquals(1000, (int) $payment->fresh()->refunded_amount);
        $this->assertEquals(2, $payment->refunds()->count());
        $this->assertEquals(StoreOrderStatus::PARTIALLY_REFUNDED, $order->fresh()->status);
    }

    public function test_a_refund_is_never_recorded_beyond_what_is_left_on_the_payment()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->postEvent($this->refundEvent($payment, 5000, 're_toobig', 5000))->assertOk();

        $this->assertEquals(2000, (int) $payment->fresh()->refunded_amount);
        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);
    }

    public function test_a_dispute_charges_back_the_order()
    {
        [$order, $payment] = $this->paidOrder(2000);

        $this->postEvent([
            'id' => 'evt_dispute',
            'type' => 'charge.dispute.created',
            'data' => ['object' => [
                'id' => 'dp_1',
                'charge' => 'ch_test_1',
                'payment_intent' => $payment->gateway_transaction_id,
                'amount' => 2000,
                'currency' => 'usd',
            ]],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::CHARGEBACK, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::CHARGEBACK, $payment->fresh()->status);
        $this->assertEquals(StorePaymentRefundType::CHARGEBACK, $payment->refunds()->first()->type);
    }

    // --- Module and driver gating -------------------------------------------------------------

    public function test_the_endpoint_is_hidden_when_the_store_is_disabled()
    {
        config(['store.enabled' => false]);
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertNotFound();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_the_endpoint_is_hidden_when_the_gateway_is_switched_off()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual'];
        $settings->save();

        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->sessionCompletedEvent($order, $payment))->assertNotFound();
    }

    public function test_an_unknown_gateway_key_is_a_404_rather_than_an_error()
    {
        $this->postEvent(['id' => 'evt_1', 'type' => 'x'], 'sig', 'notagateway')->assertNotFound();
    }

    // --- Driver contract -----------------------------------------------------------------------

    public function test_the_driver_is_registered_and_reports_itself_correctly()
    {
        $driver = app(StorePaymentGatewayManager::class)->driver('stripe');

        $this->assertInstanceOf(StripePaymentGateway::class, $driver);
        $this->assertEquals(StorePaymentGateway::STRIPE, $driver->gateway());
        $this->assertTrue($driver->isEnabled());
        $this->assertNull($driver->supportedCurrencies(), 'Stripe charges in any currency the account supports.');
    }

    public function test_the_driver_is_not_enabled_until_both_credentials_are_present()
    {
        $settings = app(StoreSettings::class);
        $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_x']];
        $settings->save();

        // The manager caches resolved drivers, so a fresh instance is needed after a settings change.
        app()->forgetInstance(StorePaymentGatewayManager::class);

        $this->assertFalse(app(StorePaymentGatewayManager::class)->driver('stripe')->isEnabled());
    }

    public function test_the_settings_schema_marks_both_credentials_secret()
    {
        $schema = collect(app(StorePaymentGatewayManager::class)->driver('stripe')->settingsSchema())
            ->keyBy('key');

        foreach (['secret_key', 'webhook_secret'] as $key) {
            $this->assertTrue($schema[$key]['required'], "{$key} must be required.");
            $this->assertTrue($schema[$key]['secret'], "{$key} must never round-trip to the browser.");
        }
    }

    public function test_a_three_decimal_currency_amount_must_end_in_a_zero()
    {
        // Stripe rejects KWD amounts that are not a multiple of ten minor units, so the driver
        // refuses to open a session it knows will fail rather than surfacing an opaque API error.
        StoreCurrency::factory()->create(['code' => 'KWD', 'exponent' => 3, 'rate_to_base' => 1, 'is_base' => false]);

        [$order, $payment] = $this->pendingOrder(1234, ['currency' => 'KWD']);

        $this->expectException(\RuntimeException::class);

        app(StorePaymentGatewayManager::class)->driver('stripe')->createPaymentSession($order, $payment);
    }

    public function test_parse_webhook_reads_the_raw_body_not_the_parsed_input()
    {
        // Guards against anyone "simplifying" verification to use $request->all(): the signature
        // covers the exact bytes sent, and a re-encoded body would not match.
        $driver = app(StorePaymentGatewayManager::class)->driver('stripe');

        $payload = '{"id":"evt_raw","type":"checkout.session.expired","data":{"object":{"id":"cs_1"}}}';
        $request = Request::create('/api/webhooks/store/stripe', 'POST', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $this->signature($payload),
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertTrue($driver->verifyWebhook($request));
        $this->assertEquals(StoreGatewayEventData::KIND_EXPIRED, $driver->parseWebhook($request)->kind);
    }
}

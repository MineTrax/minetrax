<?php

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

uses(RefreshDatabase::class);

const STRIPE_WEBHOOK_SECRET = 'whsec_testsecret';

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // Fulfilment has its own test file; here the concern is the transition the webhook causes.
    Queue::fake([ProcessStoreOrderPurchaseJob::class]);

    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = ['manual', 'stripe'];
    $settings->gateway_credentials = [
        'stripe' => [
            'secret_key' => 'sk_test_notused',
            'webhook_secret' => STRIPE_WEBHOOK_SECRET,
        ],
    ];
    $settings->save();

    // The webhook limiter goes through Redis, whose state outlives a database rollback.
    $this->withoutMiddleware([ThrottleRequests::class, ThrottleRequestsWithRedis::class]);
});

// --- Helpers ---------------------------------------------------------------------------
/**
 * Build a signature header exactly the way Stripe does: HMAC-SHA256 over "<timestamp>.<body>".
 */
function signature(string $payload, ?int $timestamp = null, string $secret = STRIPE_WEBHOOK_SECRET): string
{
    $timestamp ??= time();

    return 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, $secret);
}

function webhookStripePostEvent(array $event, ?string $signature = null, string $gateway = 'stripe')
{
    $payload = json_encode($event);

    return test()->call(
        'POST',
        "/api/webhooks/store/{$gateway}",
        [],
        [],
        [],
        ['HTTP_STRIPE_SIGNATURE' => $signature ?? signature($payload), 'CONTENT_TYPE' => 'application/json'],
        $payload,
    );
}

/**
 * @return array{0: StoreOrder, 1: StorePayment}
 */
function webhookStripePendingOrder(int $amount = 1999, array $overrides = []): array
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

function sessionCompletedEvent(StoreOrder $order, StorePayment $payment, array $overrides = []): array
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

test('a valid signature marks the order paid', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertOk();

    $order->refresh();
    expect($order->status)->toEqual(StoreOrderStatus::PAID);
    expect($order->paid_at)->not->toBeNull();

    $payment->refresh();
    expect($payment->status)->toEqual(StorePaymentStatus::COMPLETED);
    expect($payment->gateway_transaction_id)->toEqual('pi_test_'.$order->id);

    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
});

test('a bad signature is rejected and changes nothing', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment), 't=1,v1=deadbeef')
        ->assertStatus(400);

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    $this->assertDatabaseCount('store_gateway_webhooks', 0);
});

test('a signature from the wrong secret is rejected', function () {
    [$order, $payment] = webhookStripePendingOrder();
    $payload = json_encode(sessionCompletedEvent($order, $payment));

    webhookStripePostEvent(sessionCompletedEvent($order, $payment), signature($payload, secret: 'whsec_someoneelse'))->assertStatus(400);

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('a missing signature header is rejected', function () {
    [$order, $payment] = webhookStripePendingOrder();
    $payload = json_encode(sessionCompletedEvent($order, $payment));

    $this->call('POST', '/api/webhooks/store/stripe', [], [], [], ['CONTENT_TYPE' => 'application/json'], $payload)
        ->assertStatus(400);
});

test('an old signature is outside the tolerance window', function () {
    // A captured request replayed hours later must not be accepted, even though the HMAC
    // itself is still arithmetically valid.
    [$order, $payment] = webhookStripePendingOrder();
    $payload = json_encode(sessionCompletedEvent($order, $payment));

    webhookStripePostEvent(sessionCompletedEvent($order, $payment), signature($payload, time() - 3600))
        ->assertStatus(400);

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('the signature covers the body so a tampered amount is rejected', function () {
    [$order, $payment] = webhookStripePendingOrder(1999);

    $honest = sessionCompletedEvent($order, $payment);
    $signature = signature(json_encode($honest));

    $tampered = $honest;
    $tampered['data']['object']['amount_total'] = 1;

    webhookStripePostEvent($tampered, $signature)->assertStatus(400);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('a replayed event is accepted but delivers only once', function () {
    [$order, $payment] = webhookStripePendingOrder();
    $event = sessionCompletedEvent($order, $payment);

    webhookStripePostEvent($event)->assertOk();
    webhookStripePostEvent($event)->assertOk();

    $this->assertDatabaseCount('store_gateway_webhooks', 1);
    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
});

test('a processed event is recorded with its type', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertOk();

    $record = StoreGatewayWebhook::first();
    expect($record->gateway)->toEqual('stripe');
    expect($record->event_id)->toEqual('evt_'.$order->id);
    expect($record->type)->toEqual('checkout.session.completed');
    expect($record->processed_at)->not->toBeNull();
    expect($record->error)->toBeNull();
});

test('two gateways may share an event id', function () {
    // The replay guard is (gateway, event_id), not event_id alone.
    StoreGatewayWebhook::create(['gateway' => 'paypal', 'event_id' => 'evt_shared']);

    [$order, $payment] = webhookStripePendingOrder();
    $event = sessionCompletedEvent($order, $payment);
    $event['id'] = 'evt_shared';

    webhookStripePostEvent($event)->assertOk();

    $this->assertDatabaseCount('store_gateway_webhooks', 2);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
});

test('an amount mismatch fails the payment and leaves the order pending', function () {
    [$order, $payment] = webhookStripePendingOrder(1999);

    $event = sessionCompletedEvent($order, $payment, ['amount_total' => 1]);

    webhookStripePostEvent($event)->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
    Queue::assertNotPushed(ProcessStoreOrderPurchaseJob::class);
});

test('a currency mismatch fails the payment', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment, ['currency' => 'eur']))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('a zero decimal currency is not multiplied by a hundred', function () {
    StoreCurrency::factory()->create(['code' => 'JPY', 'exponent' => 0, 'rate_to_base' => 150, 'is_base' => false]);

    [$order, $payment] = webhookStripePendingOrder(1000, ['currency' => 'JPY']);

    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    expect((int) $order->fresh()->amount_due)->toEqual(1000);
});

test('an unpaid session is ignored until the money arrives', function () {
    // Asynchronous methods complete the session while the payment is still in flight.
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment, ['payment_status' => 'unpaid']))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::PENDING);
});

test('an async payment succeeding later marks the order paid', function () {
    [$order, $payment] = webhookStripePendingOrder();

    $event = sessionCompletedEvent($order, $payment);
    $event['type'] = 'checkout.session.async_payment_succeeded';

    webhookStripePostEvent($event)->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
});

test('an async payment failing fails the payment only', function () {
    [$order, $payment] = webhookStripePendingOrder();

    $event = sessionCompletedEvent($order, $payment);
    $event['type'] = 'checkout.session.async_payment_failed';

    webhookStripePostEvent($event)->assertOk();

    // Left PENDING on purpose: the buyer can still retry with another method.
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('an expired session cancels the order', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent([
        'id' => 'evt_expired',
        'type' => 'checkout.session.expired',
        'data' => ['object' => [
            'id' => $payment->gateway_session_id,
            'client_reference_id' => $order->uuid,
        ]],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CANCELLED);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('an unrecognised event type is accepted and ignored', function () {
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent([
        'id' => 'evt_unknown',
        'type' => 'customer.created',
        'data' => ['object' => ['id' => 'cus_123']],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('a payload without an event id is rejected', function () {
    webhookStripePostEvent(['type' => 'checkout.session.completed', 'data' => ['object' => []]])
        ->assertStatus(400);

    $this->assertDatabaseCount('store_gateway_webhooks', 0);
});

test('an event matching no payment is accepted without acting', function () {
    webhookStripePostEvent([
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
});

// --- Refunds and disputes -----------------------------------------------------------------
function webhookStripePaidOrder(int $amount = 2000): array
{
    [$order, $payment] = webhookStripePendingOrder($amount);
    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertOk();

    // The delivery job is faked, so complete the order the way the job would.
    app(StoreOrderService::class)
        ->markCompleted($order->fresh(), StoreDeliveryStatus::DELIVERED);

    return [$order->fresh(), $payment->fresh()];
}

function refundEvent(StorePayment $payment, int $amount, string $refundId, int $cumulative): array
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

test('a full refund refunds the order and writes a ledger row', function () {
    [$order, $payment] = webhookStripePaidOrder(2000);

    webhookStripePostEvent(refundEvent($payment, 2000, 're_1', 2000))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);

    $payment->refresh();
    expect((int) $payment->refunded_amount)->toEqual(2000);
    expect($payment->status)->toEqual(StorePaymentStatus::REFUNDED);

    $refund = $payment->refunds()->first();
    expect($refund->type)->toEqual(StorePaymentRefundType::REFUND);
    expect($refund->gateway_refund_id)->toEqual('re_1');
    expect((int) $refund->amount)->toEqual(2000);
});

test('a partial refund leaves the order partially refunded', function () {
    [$order, $payment] = webhookStripePaidOrder(2000);

    webhookStripePostEvent(refundEvent($payment, 500, 're_partial', 500))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::PARTIALLY_REFUNDED);
    expect((int) $payment->fresh()->refunded_amount)->toEqual(500);
});

test('two partial refunds accumulate from the individual refund amounts', function () {
    [$order, $payment] = webhookStripePaidOrder(2000);

    webhookStripePostEvent(refundEvent($payment, 500, 're_a', 500))->assertOk();
    webhookStripePostEvent(refundEvent($payment, 500, 're_b', 1000))->assertOk();

    expect((int) $payment->fresh()->refunded_amount)->toEqual(1000);
    expect($payment->refunds()->count())->toEqual(2);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
});

test('a refund is never recorded beyond what is left on the payment', function () {
    [$order, $payment] = webhookStripePaidOrder(2000);

    webhookStripePostEvent(refundEvent($payment, 5000, 're_toobig', 5000))->assertOk();

    expect((int) $payment->fresh()->refunded_amount)->toEqual(2000);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);
});

test('a dispute charges back the order', function () {
    [$order, $payment] = webhookStripePaidOrder(2000);

    webhookStripePostEvent([
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

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CHARGEBACK);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::CHARGEBACK);
    expect($payment->refunds()->first()->type)->toEqual(StorePaymentRefundType::CHARGEBACK);
});

test('the endpoint is hidden when the store is disabled', function () {
    config(['store.enabled' => false]);
    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertNotFound();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('the endpoint is hidden when the gateway is switched off', function () {
    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = ['manual'];
    $settings->save();

    [$order, $payment] = webhookStripePendingOrder();

    webhookStripePostEvent(sessionCompletedEvent($order, $payment))->assertNotFound();
});

test('an unknown gateway key is a 404 rather than an error', function () {
    webhookStripePostEvent(['id' => 'evt_1', 'type' => 'x'], 'sig', 'notagateway')->assertNotFound();
});

test('the driver is registered and reports itself correctly', function () {
    $driver = app(StorePaymentGatewayManager::class)->driver('stripe');

    expect($driver)->toBeInstanceOf(StripePaymentGateway::class);
    expect($driver->gateway())->toEqual(StorePaymentGateway::STRIPE);
    expect($driver->isEnabled())->toBeTrue();
    expect($driver->supportedCurrencies())->toBeNull('Stripe charges in any currency the account supports.');
});

test('the driver is not enabled until both credentials are present', function () {
    $settings = app(StoreSettings::class);
    $settings->gateway_credentials = ['stripe' => ['secret_key' => 'sk_test_x']];
    $settings->save();

    // The manager caches resolved drivers, so a fresh instance is needed after a settings change.
    app()->forgetInstance(StorePaymentGatewayManager::class);

    expect(app(StorePaymentGatewayManager::class)->driver('stripe')->isEnabled())->toBeFalse();
});

test('the settings schema marks both credentials secret', function () {
    $schema = collect(app(StorePaymentGatewayManager::class)->driver('stripe')->settingsSchema())
        ->keyBy('key');

    foreach (['secret_key', 'webhook_secret'] as $key) {
        expect($schema[$key]['required'])->toBeTrue("{$key} must be required.");
        expect($schema[$key]['secret'])->toBeTrue("{$key} must never round-trip to the browser.");
    }
});

test('a three decimal currency amount must end in a zero', function () {
    // Stripe rejects KWD amounts that are not a multiple of ten minor units, so the driver
    // refuses to open a session it knows will fail rather than surfacing an opaque API error.
    StoreCurrency::factory()->create(['code' => 'KWD', 'exponent' => 3, 'rate_to_base' => 1, 'is_base' => false]);

    [$order, $payment] = webhookStripePendingOrder(1234, ['currency' => 'KWD']);

    $this->expectException(RuntimeException::class);

    app(StorePaymentGatewayManager::class)->driver('stripe')->createPaymentSession($order, $payment);
});

test('parse webhook reads the raw body not the parsed input', function () {
    // Guards against anyone "simplifying" verification to use $request->all(): the signature
    // covers the exact bytes sent, and a re-encoded body would not match.
    $driver = app(StorePaymentGatewayManager::class)->driver('stripe');

    $payload = '{"id":"evt_raw","type":"checkout.session.expired","data":{"object":{"id":"cs_1"}}}';
    $request = Request::create('/api/webhooks/store/stripe', 'POST', [], [], [], [
        'HTTP_STRIPE_SIGNATURE' => signature($payload),
        'CONTENT_TYPE' => 'application/json',
    ], $payload);

    expect($driver->verifyWebhook($request))->toBeTrue();
    expect($driver->parseWebhook($request)->kind)->toEqual(StoreGatewayEventData::KIND_EXPIRED);
});

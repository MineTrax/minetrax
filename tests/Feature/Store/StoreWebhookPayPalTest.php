<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentRefundType;
use App\Enums\StorePaymentStatus;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Services\StoreCurrencyService;
use App\Settings\StoreSettings;
use App\Utils\Payments\PayPalPaymentGateway;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Queue::fake([ProcessStoreOrderPurchaseJob::class]);

    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = ['manual', 'paypal'];
    $settings->gateway_credentials = [
        'paypal' => [
            'mode' => 'sandbox',
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'webhook_id' => 'WH-TEST-ID',
        ],
    ];
    $settings->save();

    // The webhook limiter goes through Redis, whose state outlives a database rollback.
    $this->withoutMiddleware([ThrottleRequests::class, ThrottleRequestsWithRedis::class]);
});

// --- Helpers ---------------------------------------------------------------------------
/**
 * Every PayPal call this driver makes, faked. `verification` flips the signature check, and
 * `capture` stands in for the capture endpoint.
 */
function fakePayPal(array $overrides = []): void
{
    $defaults = [
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'A21AA-test-token',
            'expires_in' => 3600,
        ]),
        'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
            'verification_status' => 'SUCCESS',
        ]),
    ];

    Http::fake(array_merge($defaults, $overrides));
}

function captureResponse(string $captureId, string $value, string $currency, string $status = 'COMPLETED'): array
{
    return [
        'id' => 'PAYPAL-ORDER-1',
        'status' => 'COMPLETED',
        'purchase_units' => [[
            'payments' => ['captures' => [[
                'id' => $captureId,
                'status' => $status,
                'amount' => ['currency_code' => $currency, 'value' => $value],
                'custom_id' => 'unused',
                'seller_receivable_breakdown' => [
                    'paypal_fee' => ['currency_code' => $currency, 'value' => '0.75'],
                ],
            ]]],
        ]],
    ];
}

function webhookPayPalPostEvent(array $event, array $headers = [], string $gateway = 'paypal')
{
    $payload = json_encode($event);

    $default = [
        'HTTP_PAYPAL_AUTH_ALGO' => 'SHA256withRSA',
        'HTTP_PAYPAL_CERT_URL' => 'https://api.sandbox.paypal.com/v1/notifications/certs/CERT-1',
        'HTTP_PAYPAL_TRANSMISSION_ID' => 'txn-1',
        'HTTP_PAYPAL_TRANSMISSION_SIG' => 'signature',
        'HTTP_PAYPAL_TRANSMISSION_TIME' => now()->toIso8601String(),
        'CONTENT_TYPE' => 'application/json',
    ];

    return test()->call('POST', "/api/webhooks/store/{$gateway}", [], [], [], array_merge($default, $headers), $payload);
}

/**
 * @return array{0: StoreOrder, 1: StorePayment}
 */
function webhookPayPalPendingOrder(int $amount = 1999, array $overrides = []): array
{
    $order = StoreOrder::factory()->create(array_merge([
        'total' => $amount,
        'amount_due' => $amount,
        'currency' => 'USD',
        'gateway' => StorePaymentGateway::PAYPAL,
    ], $overrides));

    $payment = StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'gateway' => StorePaymentGateway::PAYPAL,
        'gateway_session_id' => 'PAYPAL-ORDER-'.$order->id,
        'amount' => $order->amount_due,
        'currency' => $order->currency,
    ]);

    return [$order, $payment];
}

function captureCompletedEvent(StoreOrder $order, StorePayment $payment, array $overrides = []): array
{
    return [
        'id' => 'WH-'.$order->id,
        'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
        'resource' => array_merge([
            'id' => 'CAPTURE-'.$order->id,
            'status' => 'COMPLETED',
            'amount' => [
                'currency_code' => $order->currency,
                'value' => app(StoreCurrencyService::class)
                    ->toDecimal((int) $order->amount_due, $order->currency),
            ],
            'custom_id' => $order->uuid,
            'supplementary_data' => ['related_ids' => ['order_id' => $payment->gateway_session_id]],
            'seller_receivable_breakdown' => [
                'paypal_fee' => ['currency_code' => $order->currency, 'value' => '0.75'],
            ],
        ], $overrides),
    ];
}

test('the driver is registered and reports itself configured', function () {
    $driver = app(StorePaymentGatewayManager::class)->driverOrFail('paypal');

    expect($driver)->toBeInstanceOf(PayPalPaymentGateway::class);
    expect($driver->isEnabled())->toBeTrue();
    expect($driver->gateway())->toBe(StorePaymentGateway::PAYPAL);
});

test('a missing credential leaves the driver disabled', function () {
    $settings = app(StoreSettings::class);
    $settings->gateway_credentials = ['paypal' => ['mode' => 'sandbox', 'client_id' => 'only-this']];
    $settings->save();

    expect(app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->isEnabled())->toBeFalse();
});

test('paypal is not offered for a currency it cannot charge', function () {
    // KWD is a real store currency and not a PayPal one, so the gateway is withheld rather than
    // failing once the buyer is already on PayPal's page.
    $manager = app(StorePaymentGatewayManager::class);

    expect($manager->availableFor('USD')->has('paypal'))->toBeTrue();
    expect($manager->availableFor('KWD')->has('paypal'))->toBeFalse();
});

test('a webhook paypal does not vouch for is rejected', function () {
    fakePayPal([
        'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
            'verification_status' => 'FAILURE',
        ]),
    ]);

    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertStatus(400);

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
});

test('a webhook with missing signature headers is rejected without calling paypal', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment), [
        'HTTP_PAYPAL_TRANSMISSION_SIG' => '',
    ])->assertStatus(400);

    Http::assertNothingSent();
});

test('a cert url that is not paypals is refused', function () {
    // PayPal fetches whatever cert URL the request carries. Refusing a stranger's host costs
    // nothing and keeps it out of the exchange entirely.
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment), [
        'HTTP_PAYPAL_CERT_URL' => 'https://evil.example.com/certs/CERT-1',
    ])->assertStatus(400);

    Http::assertNothingSent();
});

test('a completed capture marks the order paid', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertOk();

    $order->refresh();
    $payment->refresh();

    expect($order->status)->toEqual(StoreOrderStatus::PAID);
    expect($payment->status)->toEqual(StorePaymentStatus::COMPLETED);
    expect($payment->gateway_transaction_id)->toBe('CAPTURE-'.$order->id);
    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
});

test('the same event delivered twice pays the order once', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();
    $event = captureCompletedEvent($order, $payment);

    webhookPayPalPostEvent($event)->assertOk();
    webhookPayPalPostEvent($event)->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
    $this->assertDatabaseCount('store_gateway_webhooks', 1);
});

test('an amount that does not match fails the payment and leaves the order alone', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder(1999);

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment, [
        'amount' => ['currency_code' => 'USD', 'value' => '5.00'],
    ]))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('a zero decimal amount is read as whole units', function () {
    // ¥3000 is 3000 minor units, not 300000. Reading PayPal's "3000" with a hardcoded factor of
    // a hundred would reject a correct payment as a mismatch.
    fakePayPal();
    StoreCurrency::factory()->zeroDecimal()->create();

    [$order, $payment] = webhookPayPalPendingOrder(3000, ['currency' => 'JPY', 'exchange_rate' => 150]);

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment, [
        'amount' => ['currency_code' => 'JPY', 'value' => '3000'],
    ]))->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    expect((int) $payment->fresh()->amount)->toBe(3000);
});

test('a declined capture fails the payment but keeps the order payable', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent([
        'id' => 'WH-denied-'.$order->id,
        'event_type' => 'PAYMENT.CAPTURE.DENIED',
        'resource' => [
            'id' => 'CAPTURE-denied',
            'custom_id' => $order->uuid,
            'supplementary_data' => ['related_ids' => ['order_id' => $payment->gateway_session_id]],
        ],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::FAILED);
});

test('an approved order is captured rather than trusted', function () {
    // Approval means the buyer clicked pay on PayPal's page; the money only moves on capture.
    // Capturing from this webhook is what covers a buyer who closes the tab straight afterwards.
    [$order, $payment] = webhookPayPalPendingOrder(1999);

    fakePayPal([
        'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
            captureResponse('CAPTURE-from-approval', '19.99', 'USD')
        ),
    ]);

    webhookPayPalPostEvent([
        'id' => 'WH-approved-'.$order->id,
        'event_type' => 'CHECKOUT.ORDER.APPROVED',
        'resource' => [
            'id' => $payment->gateway_session_id,
            'status' => 'APPROVED',
            'purchase_units' => [['custom_id' => $order->uuid]],
        ],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    expect($payment->fresh()->gateway_transaction_id)->toBe('CAPTURE-from-approval');
});

test('an approval that cannot be captured changes nothing', function () {
    [$order, $payment] = webhookPayPalPendingOrder();

    fakePayPal([
        'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(['name' => 'UNPROCESSABLE_ENTITY'], 422),
        'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(['status' => 'APPROVED'], 200),
    ]);

    webhookPayPalPostEvent([
        'id' => 'WH-approved-fail-'.$order->id,
        'event_type' => 'CHECKOUT.ORDER.APPROVED',
        'resource' => ['id' => $payment->gateway_session_id, 'purchase_units' => [['custom_id' => $order->uuid]]],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PENDING);
    expect($payment->fresh()->status)->toEqual(StorePaymentStatus::PENDING);
});

test('an already captured order is read back rather than treated as a failure', function () {
    // The return URL usually captures first, so the approval webhook arrives to find the job
    // already done. That is a success with a transaction id, not an error.
    [$order, $payment] = webhookPayPalPendingOrder(1999);

    fakePayPal([
        'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [['issue' => 'ORDER_ALREADY_CAPTURED']],
        ], 422),
        'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(
            captureResponse('CAPTURE-existing', '19.99', 'USD')
        ),
    ]);

    webhookPayPalPostEvent([
        'id' => 'WH-approved-again-'.$order->id,
        'event_type' => 'CHECKOUT.ORDER.APPROVED',
        'resource' => ['id' => $payment->gateway_session_id, 'purchase_units' => [['custom_id' => $order->uuid]]],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    expect($payment->fresh()->gateway_transaction_id)->toBe('CAPTURE-existing');
});

test('a refund event records the refund against the capture', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder(2000);
    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertOk();

    $captureId = $payment->fresh()->gateway_transaction_id;

    webhookPayPalPostEvent([
        'id' => 'WH-refund-'.$order->id,
        'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
        'resource' => [
            'id' => 'REFUND-1',
            'amount' => ['currency_code' => 'USD', 'value' => '20.00'],
            'links' => [
                ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/v2/payments/refunds/REFUND-1'],
                ['rel' => 'up', 'href' => 'https://api-m.sandbox.paypal.com/v2/payments/captures/'.$captureId],
            ],
        ],
    ])->assertOk();

    $this->assertDatabaseHas('store_payment_refunds', [
        'gateway_refund_id' => 'REFUND-1',
        'type' => StorePaymentRefundType::REFUND->value,
        'amount' => 2000,
    ]);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);
});

test('a reversal is treated as a chargeback and revokes the grants', function () {
    fakePayPal();
    $package = StorePackage::factory()->create();
    [$order, $payment] = webhookPayPalPendingOrder(2000);
    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertOk();

    $item = $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 2000,
        'unit_price' => 2000,
        'total' => 2000,
    ]);
    $grant = $item->grant()->create([
        'store_package_id' => $package->id,
        'player_uuid' => $order->player_uuid,
        'status' => StorePackageGrantStatus::ACTIVE,
        'granted_at' => now(),
    ]);

    $captureId = $payment->fresh()->gateway_transaction_id;

    webhookPayPalPostEvent([
        'id' => 'WH-reversal-'.$order->id,
        'event_type' => 'PAYMENT.CAPTURE.REVERSED',
        'resource' => [
            'id' => 'REVERSAL-1',
            'amount' => ['currency_code' => 'USD', 'value' => '20.00'],
            'links' => [
                ['rel' => 'up', 'href' => 'https://api-m.sandbox.paypal.com/v2/payments/captures/'.$captureId],
            ],
        ],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CHARGEBACK);
    expect($grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
    $this->assertDatabaseHas('store_payment_refunds', [
        'gateway_refund_id' => 'REVERSAL-1',
        'type' => StorePaymentRefundType::CHARGEBACK->value,
    ]);
});

test('a dispute notice on its own moves nothing', function () {
    // A dispute being opened is not money leaving. Only the reversal is.
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();
    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertOk();

    webhookPayPalPostEvent([
        'id' => 'WH-dispute-'.$order->id,
        'event_type' => 'CUSTOMER.DISPUTE.CREATED',
        'resource' => ['dispute_id' => 'PP-D-1'],
    ])->assertOk();

    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PAID);
    $this->assertDatabaseCount('store_payment_refunds', 0);
});

test('creating a session sends a decimal amount and returns the approval url', function () {
    fakePayPal([
        'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-ORDER-NEW',
            'status' => 'CREATED',
            'links' => [
                ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-ORDER-NEW'],
                ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-NEW'],
            ],
        ]),
    ]);

    [$order, $payment] = webhookPayPalPendingOrder(1999);

    $session = app(StorePaymentGatewayManager::class)
        ->driverOrFail('paypal')
        ->createPaymentSession($order, $payment);

    expect($session->redirectUrl)->toBe('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-NEW');
    expect($session->sessionId)->toBe('PAYPAL-ORDER-NEW');

    Http::assertSent(function ($request) use ($order) {
        if (! str_contains($request->url(), '/v2/checkout/orders')) {
            return false;
        }

        // PayPal takes decimal strings, never minor units.
        return data_get($request->data(), 'purchase_units.0.amount.value') === '19.99'
            && data_get($request->data(), 'purchase_units.0.custom_id') === $order->uuid;
    });
});

test('a zero decimal currency is sent without decimals', function () {
    StoreCurrency::factory()->zeroDecimal()->create();

    fakePayPal([
        'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-ORDER-JPY',
            'links' => [['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=JPY']],
        ]),
    ]);

    [$order, $payment] = webhookPayPalPendingOrder(3000, ['currency' => 'JPY', 'exchange_rate' => 150]);

    app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->createPaymentSession($order, $payment);

    // ¥3000 goes out as "3000". PayPal rejects "3000.00" for yen, and "30.00" would undercharge.
    Http::assertSent(fn ($request) => str_contains($request->url(), '/v2/checkout/orders')
        && data_get($request->data(), 'purchase_units.0.amount.value') === '3000');
});

test('a refund is issued against the capture id', function () {
    fakePayPal([
        'api-m.sandbox.paypal.com/v2/payments/captures/*/refund' => Http::response([
            'id' => 'REFUND-ISSUED',
            'status' => 'COMPLETED',
        ]),
    ]);

    [$order, $payment] = webhookPayPalPendingOrder(2000);
    $payment->update(['gateway_transaction_id' => 'CAPTURE-XYZ']);

    $refundId = app(StorePaymentGatewayManager::class)
        ->driverOrFail('paypal')
        ->refund($payment->fresh(), 500, 'Partial refund');

    expect($refundId)->toBe('REFUND-ISSUED');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v2/payments/captures/CAPTURE-XYZ/refund')
        && data_get($request->data(), 'amount.value') === '5.00');
});

test('a refund without a capture id is refused before any call', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    $this->expectException(RuntimeException::class);

    app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->refund($payment, 500);
});

test('bad credentials surface as a clear error', function () {
    Http::fake([
        'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'error' => 'invalid_client',
            'error_description' => 'Client Authentication failed',
        ], 401),
    ]);

    [$order, $payment] = webhookPayPalPendingOrder();

    $this->expectExceptionMessage('Client Authentication failed');

    app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->createPaymentSession($order, $payment);
});

test('the token is fetched once and reused', function () {
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    $first = captureCompletedEvent($order, $payment);
    $second = array_merge($first, ['id' => 'WH-second']);

    webhookPayPalPostEvent($first)->assertOk();
    webhookPayPalPostEvent($second)->assertOk();

    $tokenCalls = 0;
    Http::recorded(function ($request) use (&$tokenCalls) {
        if (str_contains($request->url(), '/v1/oauth2/token')) {
            $tokenCalls++;
        }
    });

    expect($tokenCalls)->toBe(1, 'The access token should be cached between calls.');
});

test('the webhook endpoint is closed when the module is disabled', function () {
    config(['store.enabled' => false]);
    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertStatus(404);
});

test('the webhook endpoint is closed when paypal is switched off', function () {
    $settings = app(StoreSettings::class);
    $settings->enabled_gateways = ['manual'];
    $settings->save();

    fakePayPal();
    [$order, $payment] = webhookPayPalPendingOrder();

    webhookPayPalPostEvent(captureCompletedEvent($order, $payment))->assertStatus(404);
});

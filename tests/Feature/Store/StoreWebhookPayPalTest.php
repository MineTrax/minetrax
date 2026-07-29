<?php

namespace Tests\Feature\Store;

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
use Tests\TestCase;

/**
 * The PayPal driver, exercised end to end with Http::fake and no credentials.
 *
 * PayPal verifies webhooks by calling them back rather than with a local HMAC, so the fake stands in
 * for that call. What is actually under test is everything around it: that verification is required
 * at all, that approval is not treated as payment, that amounts convert both ways through the
 * currency's own exponent, and that every event maps onto the right order transition.
 */
class StoreWebhookPayPalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    // --- Helpers ---------------------------------------------------------------------------

    /**
     * Every PayPal call this driver makes, faked. `verification` flips the signature check, and
     * `capture` stands in for the capture endpoint.
     */
    private function fakePayPal(array $overrides = []): void
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

    private function captureResponse(string $captureId, string $value, string $currency, string $status = 'COMPLETED'): array
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

    private function postEvent(array $event, array $headers = [], string $gateway = 'paypal')
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

        return $this->call('POST', "/api/webhooks/store/{$gateway}", [], [], [], array_merge($default, $headers), $payload);
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

    private function captureCompletedEvent(StoreOrder $order, StorePayment $payment, array $overrides = []): array
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

    // --- The driver in the registry ---------------------------------------------------------

    public function test_the_driver_is_registered_and_reports_itself_configured()
    {
        $driver = app(StorePaymentGatewayManager::class)->driverOrFail('paypal');

        $this->assertInstanceOf(PayPalPaymentGateway::class, $driver);
        $this->assertTrue($driver->isEnabled());
        $this->assertSame(StorePaymentGateway::PAYPAL, $driver->gateway());
    }

    public function test_a_missing_credential_leaves_the_driver_disabled()
    {
        $settings = app(StoreSettings::class);
        $settings->gateway_credentials = ['paypal' => ['mode' => 'sandbox', 'client_id' => 'only-this']];
        $settings->save();

        $this->assertFalse(app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->isEnabled());
    }

    public function test_paypal_is_not_offered_for_a_currency_it_cannot_charge()
    {
        // KWD is a real store currency and not a PayPal one, so the gateway is withheld rather than
        // failing once the buyer is already on PayPal's page.
        $manager = app(StorePaymentGatewayManager::class);

        $this->assertTrue($manager->availableFor('USD')->has('paypal'));
        $this->assertFalse($manager->availableFor('KWD')->has('paypal'));
    }

    // --- Verification ------------------------------------------------------------------------

    public function test_a_webhook_paypal_does_not_vouch_for_is_rejected()
    {
        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'FAILURE',
            ]),
        ]);

        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertStatus(400);

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
    }

    public function test_a_webhook_with_missing_signature_headers_is_rejected_without_calling_paypal()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment), [
            'HTTP_PAYPAL_TRANSMISSION_SIG' => '',
        ])->assertStatus(400);

        Http::assertNothingSent();
    }

    public function test_a_cert_url_that_is_not_paypals_is_refused()
    {
        // PayPal fetches whatever cert URL the request carries. Refusing a stranger's host costs
        // nothing and keeps it out of the exchange entirely.
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment), [
            'HTTP_PAYPAL_CERT_URL' => 'https://evil.example.com/certs/CERT-1',
        ])->assertStatus(400);

        Http::assertNothingSent();
    }

    // --- Payment ----------------------------------------------------------------------------

    public function test_a_completed_capture_marks_the_order_paid()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertOk();

        $order->refresh();
        $payment->refresh();

        $this->assertEquals(StoreOrderStatus::PAID, $order->status);
        $this->assertEquals(StorePaymentStatus::COMPLETED, $payment->status);
        $this->assertSame('CAPTURE-'.$order->id, $payment->gateway_transaction_id);
        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
    }

    public function test_the_same_event_delivered_twice_pays_the_order_once()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();
        $event = $this->captureCompletedEvent($order, $payment);

        $this->postEvent($event)->assertOk();
        $this->postEvent($event)->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        Queue::assertPushed(ProcessStoreOrderPurchaseJob::class, 1);
        $this->assertDatabaseCount('store_gateway_webhooks', 1);
    }

    public function test_an_amount_that_does_not_match_fails_the_payment_and_leaves_the_order_alone()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder(1999);

        $this->postEvent($this->captureCompletedEvent($order, $payment, [
            'amount' => ['currency_code' => 'USD', 'value' => '5.00'],
        ]))->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    public function test_a_zero_decimal_amount_is_read_as_whole_units()
    {
        // ¥3000 is 3000 minor units, not 300000. Reading PayPal's "3000" with a hardcoded factor of
        // a hundred would reject a correct payment as a mismatch.
        $this->fakePayPal();
        StoreCurrency::factory()->zeroDecimal()->create();

        [$order, $payment] = $this->pendingOrder(3000, ['currency' => 'JPY', 'exchange_rate' => 150]);

        $this->postEvent($this->captureCompletedEvent($order, $payment, [
            'amount' => ['currency_code' => 'JPY', 'value' => '3000'],
        ]))->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        $this->assertSame(3000, (int) $payment->fresh()->amount);
    }

    public function test_a_declined_capture_fails_the_payment_but_keeps_the_order_payable()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent([
            'id' => 'WH-denied-'.$order->id,
            'event_type' => 'PAYMENT.CAPTURE.DENIED',
            'resource' => [
                'id' => 'CAPTURE-denied',
                'custom_id' => $order->uuid,
                'supplementary_data' => ['related_ids' => ['order_id' => $payment->gateway_session_id]],
            ],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::FAILED, $payment->fresh()->status);
    }

    // --- Approval is not payment -------------------------------------------------------------

    public function test_an_approved_order_is_captured_rather_than_trusted()
    {
        // Approval means the buyer clicked pay on PayPal's page; the money only moves on capture.
        // Capturing from this webhook is what covers a buyer who closes the tab straight afterwards.
        [$order, $payment] = $this->pendingOrder(1999);

        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(
                $this->captureResponse('CAPTURE-from-approval', '19.99', 'USD')
            ),
        ]);

        $this->postEvent([
            'id' => 'WH-approved-'.$order->id,
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => [
                'id' => $payment->gateway_session_id,
                'status' => 'APPROVED',
                'purchase_units' => [['custom_id' => $order->uuid]],
            ],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        $this->assertSame('CAPTURE-from-approval', $payment->fresh()->gateway_transaction_id);
    }

    public function test_an_approval_that_cannot_be_captured_changes_nothing()
    {
        [$order, $payment] = $this->pendingOrder();

        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response(['name' => 'UNPROCESSABLE_ENTITY'], 422),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(['status' => 'APPROVED'], 200),
        ]);

        $this->postEvent([
            'id' => 'WH-approved-fail-'.$order->id,
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => ['id' => $payment->gateway_session_id, 'purchase_units' => [['custom_id' => $order->uuid]]],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PENDING, $order->fresh()->status);
        $this->assertEquals(StorePaymentStatus::PENDING, $payment->fresh()->status);
    }

    public function test_an_already_captured_order_is_read_back_rather_than_treated_as_a_failure()
    {
        // The return URL usually captures first, so the approval webhook arrives to find the job
        // already done. That is a success with a transaction id, not an error.
        [$order, $payment] = $this->pendingOrder(1999);

        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'name' => 'UNPROCESSABLE_ENTITY',
                'details' => [['issue' => 'ORDER_ALREADY_CAPTURED']],
            ], 422),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*' => Http::response(
                $this->captureResponse('CAPTURE-existing', '19.99', 'USD')
            ),
        ]);

        $this->postEvent([
            'id' => 'WH-approved-again-'.$order->id,
            'event_type' => 'CHECKOUT.ORDER.APPROVED',
            'resource' => ['id' => $payment->gateway_session_id, 'purchase_units' => [['custom_id' => $order->uuid]]],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        $this->assertSame('CAPTURE-existing', $payment->fresh()->gateway_transaction_id);
    }

    // --- Money going back --------------------------------------------------------------------

    public function test_a_refund_event_records_the_refund_against_the_capture()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder(2000);
        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertOk();

        $captureId = $payment->fresh()->gateway_transaction_id;

        $this->postEvent([
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
        $this->assertEquals(StoreOrderStatus::REFUNDED, $order->fresh()->status);
    }

    public function test_a_reversal_is_treated_as_a_chargeback_and_revokes_the_grants()
    {
        $this->fakePayPal();
        $package = StorePackage::factory()->create();
        [$order, $payment] = $this->pendingOrder(2000);
        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertOk();

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

        $this->postEvent([
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

        $this->assertEquals(StoreOrderStatus::CHARGEBACK, $order->fresh()->status);
        $this->assertEquals(StorePackageGrantStatus::REVOKED, $grant->fresh()->status);
        $this->assertDatabaseHas('store_payment_refunds', [
            'gateway_refund_id' => 'REVERSAL-1',
            'type' => StorePaymentRefundType::CHARGEBACK->value,
        ]);
    }

    public function test_a_dispute_notice_on_its_own_moves_nothing()
    {
        // A dispute being opened is not money leaving. Only the reversal is.
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();
        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertOk();

        $this->postEvent([
            'id' => 'WH-dispute-'.$order->id,
            'event_type' => 'CUSTOMER.DISPUTE.CREATED',
            'resource' => ['dispute_id' => 'PP-D-1'],
        ])->assertOk();

        $this->assertEquals(StoreOrderStatus::PAID, $order->fresh()->status);
        $this->assertDatabaseCount('store_payment_refunds', 0);
    }

    // --- Outbound calls ---------------------------------------------------------------------

    public function test_creating_a_session_sends_a_decimal_amount_and_returns_the_approval_url()
    {
        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-NEW',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'self', 'href' => 'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-ORDER-NEW'],
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-NEW'],
                ],
            ]),
        ]);

        [$order, $payment] = $this->pendingOrder(1999);

        $session = app(StorePaymentGatewayManager::class)
            ->driverOrFail('paypal')
            ->createPaymentSession($order, $payment);

        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-NEW', $session->redirectUrl);
        $this->assertSame('PAYPAL-ORDER-NEW', $session->sessionId);

        Http::assertSent(function ($request) use ($order) {
            if (! str_contains($request->url(), '/v2/checkout/orders')) {
                return false;
            }

            // PayPal takes decimal strings, never minor units.
            return data_get($request->data(), 'purchase_units.0.amount.value') === '19.99'
                && data_get($request->data(), 'purchase_units.0.custom_id') === $order->uuid;
        });
    }

    public function test_a_zero_decimal_currency_is_sent_without_decimals()
    {
        StoreCurrency::factory()->zeroDecimal()->create();

        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'PAYPAL-ORDER-JPY',
                'links' => [['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=JPY']],
            ]),
        ]);

        [$order, $payment] = $this->pendingOrder(3000, ['currency' => 'JPY', 'exchange_rate' => 150]);

        app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->createPaymentSession($order, $payment);

        // ¥3000 goes out as "3000". PayPal rejects "3000.00" for yen, and "30.00" would undercharge.
        Http::assertSent(fn ($request) => str_contains($request->url(), '/v2/checkout/orders')
            && data_get($request->data(), 'purchase_units.0.amount.value') === '3000');
    }

    public function test_a_refund_is_issued_against_the_capture_id()
    {
        $this->fakePayPal([
            'api-m.sandbox.paypal.com/v2/payments/captures/*/refund' => Http::response([
                'id' => 'REFUND-ISSUED',
                'status' => 'COMPLETED',
            ]),
        ]);

        [$order, $payment] = $this->pendingOrder(2000);
        $payment->update(['gateway_transaction_id' => 'CAPTURE-XYZ']);

        $refundId = app(StorePaymentGatewayManager::class)
            ->driverOrFail('paypal')
            ->refund($payment->fresh(), 500, 'Partial refund');

        $this->assertSame('REFUND-ISSUED', $refundId);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/v2/payments/captures/CAPTURE-XYZ/refund')
            && data_get($request->data(), 'amount.value') === '5.00');
    }

    public function test_a_refund_without_a_capture_id_is_refused_before_any_call()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->expectException(\RuntimeException::class);

        app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->refund($payment, 500);
    }

    public function test_bad_credentials_surface_as_a_clear_error()
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'error' => 'invalid_client',
                'error_description' => 'Client Authentication failed',
            ], 401),
        ]);

        [$order, $payment] = $this->pendingOrder();

        $this->expectExceptionMessage('Client Authentication failed');

        app(StorePaymentGatewayManager::class)->driverOrFail('paypal')->createPaymentSession($order, $payment);
    }

    public function test_the_token_is_fetched_once_and_reused()
    {
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $first = $this->captureCompletedEvent($order, $payment);
        $second = array_merge($first, ['id' => 'WH-second']);

        $this->postEvent($first)->assertOk();
        $this->postEvent($second)->assertOk();

        $tokenCalls = 0;
        Http::recorded(function ($request) use (&$tokenCalls) {
            if (str_contains($request->url(), '/v1/oauth2/token')) {
                $tokenCalls++;
            }
        });

        $this->assertSame(1, $tokenCalls, 'The access token should be cached between calls.');
    }

    public function test_the_webhook_endpoint_is_closed_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);
        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertStatus(404);
    }

    public function test_the_webhook_endpoint_is_closed_when_paypal_is_switched_off()
    {
        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual'];
        $settings->save();

        $this->fakePayPal();
        [$order, $payment] = $this->pendingOrder();

        $this->postEvent($this->captureCompletedEvent($order, $payment))->assertStatus(404);
    }
}

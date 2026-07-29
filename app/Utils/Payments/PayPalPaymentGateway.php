<?php

namespace App\Utils\Payments;

use App\Enums\StorePaymentGateway;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\Data\StorePaymentSessionData;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal, via the Orders v2 REST API over the HTTP client.
 *
 * No SDK: PayPal's own PHP SDK is deprecated and the four endpoints this needs are plain JSON.
 * Hosted approval page, like Stripe Checkout, so no card data touches the site.
 *
 * Two things make PayPal different from Stripe and shape most of what follows:
 *
 * 1. **Amounts are decimal strings**, not minor units — "20.00", not 2000. Every amount therefore
 *    goes through StoreCurrencyService, which knows each currency's exponent, in both directions.
 * 2. **Approval and capture are separate steps.** A buyer approving on PayPal's page has not paid
 *    yet. Capture happens when they return, and also from the CHECKOUT.ORDER.APPROVED webhook —
 *    otherwise a buyer who closes the tab after approving is never charged and never delivered to.
 */
class PayPalPaymentGateway extends AbstractStorePaymentGateway
{
    private const SANDBOX_BASE = 'https://api-m.sandbox.paypal.com';

    private const LIVE_BASE = 'https://api-m.paypal.com';

    /**
     * Currencies PayPal can charge. Anything else is not offered at checkout rather than failing on
     * PayPal's own page.
     */
    private const SUPPORTED_CURRENCIES = [
        'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY',
        'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD',
    ];

    /**
     * PayPal refuses decimals in these three, whatever ISO 4217 says the exponent is — HUF is a
     * two-decimal currency everywhere else, and PayPal will reject "1234.56" for it.
     */
    private const NO_DECIMAL_CURRENCIES = ['HUF', 'JPY', 'TWD'];

    public function __construct(
        StoreSettings $settings,
        private GeneralSettings $general,
        private StoreCurrencyService $currencies,
    ) {
        parent::__construct($settings);
    }

    public function gateway(): StorePaymentGateway
    {
        return StorePaymentGateway::PAYPAL;
    }

    public function label(): string
    {
        return __('PayPal');
    }

    public function description(): ?string
    {
        return __('Pay with a PayPal balance, bank account or card.');
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'mode',
                'label' => __('Environment'),
                'type' => 'select',
                'required' => true,
                'options' => [
                    'sandbox' => __('Sandbox (testing)'),
                    'live' => __('Live'),
                ],
                'help' => __('Sandbox credentials only work against sandbox, and live only against live.'),
            ],
            [
                'key' => 'client_id',
                'label' => __('Client ID'),
                'type' => 'text',
                'required' => true,
                'help' => __('PayPal Developer Dashboard → Apps & Credentials → your app.'),
            ],
            [
                'key' => 'client_secret',
                'label' => __('Client Secret'),
                'type' => 'text',
                'required' => true,
                'secret' => true,
            ],
            [
                'key' => 'webhook_id',
                'label' => __('Webhook ID'),
                'type' => 'text',
                'required' => true,
                'help' => __('Create a webhook pointing at :url, then paste the ID PayPal shows for it. Signature checking cannot work without it.', [
                    'url' => route('api.store.webhook', ['gateway' => 'paypal']),
                ]),
            ],
        ];
    }

    public function supportedCurrencies(): ?array
    {
        return self::SUPPORTED_CURRENCIES;
    }

    /**
     * Create a PayPal order and hand back its approval URL.
     *
     * One purchase unit for amount_due rather than a line per package: coupons, tax and gift-card
     * credit all move the total away from the sum of the line prices, and the figure PayPal reports
     * back is verified against amount_due before the order is marked paid.
     */
    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData
    {
        $response = $this->api()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $order->uuid,
                // Echoed back on every capture event, which is how a webhook finds the order.
                'custom_id' => $order->uuid,
                'description' => $this->description127($order),
                'amount' => [
                    'currency_code' => strtoupper($order->currency),
                    'value' => $this->amountValue((int) $order->amount_due, $order->currency),
                ],
            ]],
            'application_context' => [
                'brand_name' => $this->general->site_name ?: config('app.name'),
                'user_action' => 'PAY_NOW',
                // Nothing here is shipped, and asking for an address would only add a step.
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => route('store.order.result', $order->uuid),
                'cancel_url' => route('store.order.result', $order->uuid),
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException($this->errorMessage($response->json(), __('PayPal could not start this payment.')));
        }

        $approval = $this->linkFor($response->json('links', []), 'approve');

        if (! $approval) {
            throw new \RuntimeException(__('PayPal did not return an approval link.'));
        }

        return new StorePaymentSessionData(
            redirectUrl: $approval,
            sessionId: (string) $response->json('id'),
            raw: ['status' => $response->json('status')],
        );
    }

    /**
     * Verify with PayPal's verify-webhook-signature API.
     *
     * PayPal signs with a certificate rather than a shared secret, so verification is a call to
     * them rather than a local HMAC. The body is decoded only to be handed straight back in that
     * call — the bytes are never re-encoded and sent onward as if they were the original.
     */
    public function verifyWebhook(Request $request): bool
    {
        $webhookId = $this->credential('webhook_id');

        $headers = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];

        if (blank($webhookId) || collect($headers)->contains(fn ($value) => blank($value))) {
            return false;
        }

        // The cert URL is attacker-supplied and PayPal will fetch it. They validate the chain, but
        // refusing anything that is not theirs costs nothing and keeps a stranger's URL out of it.
        if (! $this->isPayPalHost($headers['cert_url'])) {
            Log::warning('Rejected a PayPal webhook with a foreign cert URL.', ['cert_url' => $headers['cert_url']]);

            return false;
        }

        $event = json_decode($request->getContent(), true);

        if (! is_array($event)) {
            return false;
        }

        try {
            $response = $this->api()->post('/v1/notifications/verify-webhook-signature', $headers + [
                'webhook_id' => $webhookId,
                'webhook_event' => $event,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not verify a PayPal webhook signature.', ['reason' => $e->getMessage()]);

            return false;
        }

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }

    public function parseWebhook(Request $request): StoreGatewayEventData
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $eventId = (string) data_get($payload, 'id', '');
        $type = (string) data_get($payload, 'event_type', '');
        $resource = (array) data_get($payload, 'resource', []);

        return match ($type) {
            // Approved is not paid. Capturing here is what protects the buyer who approves and then
            // closes the tab before the return URL is ever hit.
            'CHECKOUT.ORDER.APPROVED' => $this->captureApprovedOrder($eventId, $type, $resource, $payload),

            'PAYMENT.CAPTURE.COMPLETED' => $this->fromCapture($eventId, $type, $resource, $payload),

            'PAYMENT.CAPTURE.DENIED' => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_FAILED,
                eventType: $type,
                sessionId: data_get($resource, 'supplementary_data.related_ids.order_id'),
                orderUuid: data_get($resource, 'custom_id'),
                failureReason: __('PayPal declined the payment.'),
                raw: $payload,
            ),

            'PAYMENT.CAPTURE.REFUNDED' => $this->fromRefund($eventId, $type, $resource, $payload, StoreGatewayEventData::KIND_REFUNDED),

            // A reversal is money actually taken back off the account, which is the point at which
            // a dispute has been lost. CUSTOMER.DISPUTE.* events are only ever informational.
            'PAYMENT.CAPTURE.REVERSED' => $this->fromRefund($eventId, $type, $resource, $payload, StoreGatewayEventData::KIND_CHARGEBACK),

            default => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_IGNORED,
                eventType: $type,
                raw: $payload,
            ),
        };
    }

    /**
     * Capture when the buyer lands back on the result page.
     *
     * This is the ordinary path: PayPal holds an approved order until someone captures it, and doing
     * it here means the purchase is delivered while the buyer is still looking at the page.
     */
    public function confirmOnReturn(StorePayment $payment): ?StoreGatewayEventData
    {
        if (! $payment->gateway_session_id) {
            return null;
        }

        $capture = $this->captureOrder($payment->gateway_session_id);

        if (! $capture) {
            return null;
        }

        return $this->eventFromCaptureArray('return_'.$payment->gateway_session_id, null, $capture, []);
    }

    public function refund(StorePayment $payment, int $amountMinor, ?string $reason = null): string
    {
        if (! $payment->gateway_transaction_id) {
            throw new \RuntimeException(__('This payment has no PayPal capture to refund.'));
        }

        $response = $this->api()->post("/v2/payments/captures/{$payment->gateway_transaction_id}/refund", array_filter([
            'amount' => [
                'currency_code' => strtoupper($payment->currency),
                'value' => $this->amountValue($amountMinor, $payment->currency),
            ],
            'note_to_payer' => $reason ? mb_substr($reason, 0, 255) : null,
        ]));

        if ($response->failed()) {
            throw new \RuntimeException($this->errorMessage($response->json(), __('PayPal refused the refund.')));
        }

        return (string) $response->json('id');
    }

    /**
     * Capture an order PayPal says the buyer approved.
     *
     * Already-captured is a success here, not a failure: the return URL usually gets there first,
     * and this webhook is the safety net. In that case the existing capture is read back off the
     * order so the event still carries the transaction id and amount.
     */
    private function captureApprovedOrder(string $eventId, string $type, array $resource, array $payload): StoreGatewayEventData
    {
        $paypalOrderId = (string) data_get($resource, 'id', '');

        if ($paypalOrderId === '') {
            return new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_IGNORED,
                eventType: $type,
                raw: $payload,
            );
        }

        $capture = $this->captureOrder($paypalOrderId);

        if (! $capture) {
            return new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_IGNORED,
                eventType: $type,
                sessionId: $paypalOrderId,
                orderUuid: data_get($resource, 'purchase_units.0.custom_id'),
                raw: $payload,
            );
        }

        return $this->eventFromCaptureArray($eventId, $type, $capture, $payload, $paypalOrderId);
    }

    /**
     * Capture a PayPal order, or read back the capture that already exists.
     *
     * @return array<string, mixed>|null the capture object
     */
    private function captureOrder(string $paypalOrderId): ?array
    {
        $response = $this->api()->post("/v2/checkout/orders/{$paypalOrderId}/capture");

        if ($response->successful()) {
            return (array) data_get($response->json(), 'purchase_units.0.payments.captures.0');
        }

        // 422 covers both "already captured" and "not approved yet"; only the first has a capture to
        // find, so the order is fetched rather than the error message being parsed.
        $existing = $this->api()->get("/v2/checkout/orders/{$paypalOrderId}");

        if ($existing->successful()) {
            $capture = (array) data_get($existing->json(), 'purchase_units.0.payments.captures.0');

            if (($capture['status'] ?? null) === 'COMPLETED') {
                return $capture;
            }
        }

        Log::warning('PayPal capture did not complete.', [
            'paypal_order_id' => $paypalOrderId,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return null;
    }

    /**
     * @param  array<string, mixed>  $capture
     */
    private function eventFromCaptureArray(
        string $eventId,
        ?string $type,
        array $capture,
        array $payload,
        ?string $paypalOrderId = null,
    ): StoreGatewayEventData {
        $currency = strtoupper((string) data_get($capture, 'amount.currency_code', ''));
        $fee = data_get($capture, 'seller_receivable_breakdown.paypal_fee.value');

        return new StoreGatewayEventData(
            eventId: $eventId,
            kind: ($capture['status'] ?? null) === 'COMPLETED'
                ? StoreGatewayEventData::KIND_COMPLETED
                : StoreGatewayEventData::KIND_IGNORED,
            eventType: $type,
            sessionId: $paypalOrderId ?? data_get($capture, 'supplementary_data.related_ids.order_id'),
            transactionId: data_get($capture, 'id'),
            amountMinor: $this->toMinor(data_get($capture, 'amount.value'), $currency),
            currency: $currency,
            feeMinor: $fee !== null ? $this->toMinor($fee, $currency) : null,
            orderUuid: data_get($capture, 'custom_id'),
            raw: $payload ?: $capture,
        );
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    private function fromCapture(string $eventId, string $type, array $resource, array $payload): StoreGatewayEventData
    {
        return $this->eventFromCaptureArray($eventId, $type, $resource, $payload);
    }

    /**
     * A refund or a reversal.
     *
     * Neither carries the capture id as a field; the only reliable pointer is the `up` link, which
     * is the capture the money came out of. Without it there is nothing to attach the refund to.
     *
     * @param  array<string, mixed>  $resource
     */
    private function fromRefund(string $eventId, string $type, array $resource, array $payload, string $kind): StoreGatewayEventData
    {
        $currency = strtoupper((string) data_get($resource, 'amount.currency_code', ''));

        return new StoreGatewayEventData(
            eventId: $eventId,
            kind: $kind,
            eventType: $type,
            transactionId: $this->captureIdFromLinks((array) data_get($resource, 'links', [])),
            amountMinor: $this->toMinor(data_get($resource, 'amount.value'), $currency),
            currency: $currency,
            orderUuid: data_get($resource, 'custom_id'),
            refundId: data_get($resource, 'id'),
            raw: $payload,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $links
     */
    private function captureIdFromLinks(array $links): ?string
    {
        $up = $this->linkFor($links, 'up');

        if (! $up || ! preg_match('#/captures/([^/?]+)#', $up, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @param  array<int, array<string, mixed>>  $links
     */
    private function linkFor(array $links, string $rel): ?string
    {
        foreach ($links as $link) {
            if (($link['rel'] ?? null) === $rel && ! blank($link['href'] ?? null)) {
                return (string) $link['href'];
            }
        }

        return null;
    }

    /**
     * Minor units to the decimal string PayPal wants.
     *
     * The number of decimals comes from the currency, never a constant. The three currencies PayPal
     * insists have none are trimmed rather than rounded: silently dropping 56 fillér off somebody's
     * total is worse than telling the admin their HUF price cannot be charged through PayPal.
     */
    private function amountValue(int $amountMinor, string $currency): string
    {
        $code = strtoupper($currency);
        $decimal = $this->currencies->toDecimal($amountMinor, $code);

        if (! in_array($code, self::NO_DECIMAL_CURRENCIES, true) || ! str_contains($decimal, '.')) {
            return $decimal;
        }

        [$whole, $fraction] = explode('.', $decimal, 2);

        if (rtrim($fraction, '0') !== '') {
            throw new \RuntimeException(__('PayPal cannot charge fractional :currency amounts. Use a whole-number price or the nearest-whole rounding rule.', [
                'currency' => $code,
            ]));
        }

        return $whole;
    }

    /**
     * PayPal's decimal string back to minor units, via the currency's own exponent.
     */
    private function toMinor(mixed $value, string $currency): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value) || blank($currency)) {
            return null;
        }

        return $this->currencies->toMinor((string) $value, strtoupper($currency));
    }

    /**
     * PayPal caps a purchase-unit description at 127 characters.
     */
    private function description127(StoreOrder $order): string
    {
        $names = $order->loadMissing('items')->items->map(
            fn ($item) => $item->quantity > 1 ? "{$item->quantity} x {$item->package_name}" : $item->package_name
        );

        $summary = $names->implode(', ');

        if ($summary === '') {
            $summary = __('Store purchase');
        }

        return mb_substr($summary, 0, 127);
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function errorMessage(?array $body, string $fallback): string
    {
        $detail = data_get($body, 'details.0.description')
            ?? data_get($body, 'message')
            ?? data_get($body, 'error_description');

        return $detail ? $fallback.' '.$detail : $fallback;
    }

    private function isPayPalHost(?string $url): bool
    {
        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));

        return $host === 'paypal.com' || str_ends_with($host, '.paypal.com');
    }

    private function baseUrl(): string
    {
        return $this->credential('mode') === 'live' ? self::LIVE_BASE : self::SANDBOX_BASE;
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    /**
     * A client-credentials token, cached until shortly before it expires.
     *
     * Keyed by mode and client id so switching between sandbox and live, or rotating credentials,
     * cannot hand back a token minted for the other one.
     */
    private function accessToken(): string
    {
        $clientId = (string) $this->credential('client_id');
        $secret = (string) $this->credential('client_secret');

        if (blank($clientId) || blank($secret)) {
            throw new \RuntimeException(__('PayPal is missing its client ID or secret.'));
        }

        $cacheKey = 'store:paypal:token:'.sha1($this->baseUrl().'|'.$clientId);

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $response = Http::baseUrl($this->baseUrl())
            ->withBasicAuth($clientId, $secret)
            ->asForm()
            ->acceptJson()
            ->timeout(20)
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if ($response->failed() || blank($response->json('access_token'))) {
            throw new \RuntimeException($this->errorMessage($response->json(), __('PayPal rejected these credentials.')));
        }

        $token = (string) $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        // A minute of headroom, so a token cannot expire between being read and being used.
        Cache::put($cacheKey, $token, now()->addSeconds(max(60, $expiresIn - 60)));

        return $token;
    }
}

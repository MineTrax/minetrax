<?php

namespace App\Utils\Payments;

use App\Enums\StorePaymentGateway;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Services\StoreCurrencyService;
use App\Settings\GeneralSettings;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\Data\StorePaymentSessionData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\WebhookSignature;

/**
 * Stripe, via hosted Checkout Sessions.
 *
 * Hosted deliberately: the buyer is redirected to Stripe's own page, so no card data ever touches
 * this application and the site stays in PCI SAQ-A scope. That also means the only Stripe
 * credentials needed are a secret key and a webhook signing secret — there is no publishable key
 * because no Stripe.js is ever mounted client-side.
 */
class StripePaymentGateway extends AbstractStorePaymentGateway
{
    /**
     * How far outside the signature's own timestamp a webhook may be, in seconds. Stripe's own
     * default; it is what stops a captured request being replayed days later.
     */
    private const SIGNATURE_TOLERANCE = 300;

    private ?StripeClient $client = null;

    public function __construct(
        private GeneralSettings $general,
        private StoreCurrencyService $currencies,
    ) {}

    public function gateway(): StorePaymentGateway
    {
        return StorePaymentGateway::STRIPE;
    }

    /**
     * Names the processor as well as the payment method. The same label is what the admin gateway
     * screen puts above the credentials, where "Credit / Debit Card" alone left no clue which
     * provider was being configured.
     */
    public function label(): string
    {
        return __('Stripe (Credit / Debit Card)');
    }

    public function description(): ?string
    {
        return __('Pay securely with Stripe. Cards, Apple Pay and Google Pay.');
    }

    public function settingsSchema(): array
    {
        return [
            [
                'key' => 'secret_key',
                'label' => __('Secret Key'),
                'type' => 'text',
                'required' => true,
                'secret' => true,
                'help' => __('Stripe Dashboard → Developers → API keys. Starts with sk_test_ or sk_live_.'),
            ],
            [
                'key' => 'webhook_secret',
                'label' => __('Webhook Signing Secret'),
                'type' => 'text',
                'required' => true,
                'secret' => true,
                'help' => __('Starts with whsec_. Create an endpoint pointing at :url, or use the value printed by `stripe listen`.', [
                    'url' => route('api.store.webhook', ['gateway' => 'stripe']),
                ]),
            ],
            [
                'key' => 'statement_descriptor',
                'label' => __('Statement Descriptor'),
                'type' => 'text',
                'required' => false,
                'help' => __('Optional. What buyers see on their card statement. 5-22 characters.'),
            ],
        ];
    }

    /**
     * Open a hosted Checkout Session and hand back its URL.
     *
     * The charge is a single line item for amount_due rather than one line per package. Coupons,
     * tax and gift-card credit all move the total away from the sum of the line prices, and the
     * amount Stripe reports back is verified against amount_due before the order is marked paid —
     * so the one number that must be exactly right is the one that gets sent.
     */
    public function createPaymentSession(StoreOrder $order, StorePayment $payment): StorePaymentSessionData
    {
        $amount = (int) $order->amount_due;
        $currency = strtolower($order->currency);

        $this->assertChargeable($amount, $order->currency);

        $metadata = [
            'order_uuid' => $order->uuid,
            'order_id' => (string) $order->id,
            'payment_uuid' => $payment->uuid,
        ];

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            // Both are echoed back on the session object, so either can recover the order.
            'client_reference_id' => $order->uuid,
            'metadata' => $metadata,
            'payment_intent_data' => array_filter([
                'metadata' => $metadata,
                'statement_descriptor' => $this->credential('statement_descriptor') ?: null,
            ]),
            'customer_email' => $order->email ?: null,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => $this->productName($order),
                        'description' => $this->productDescription($order),
                    ],
                ],
            ]],
            'expires_at' => $this->expiresAt(),
            'success_url' => route('store.order.result', $order->uuid),
            'cancel_url' => route('store.order.result', $order->uuid),
        ]);

        return new StorePaymentSessionData(
            redirectUrl: $session->url,
            sessionId: $session->id,
            raw: ['expires_at' => $session->expires_at],
        );
    }

    /**
     * Send the buyer back to the Checkout Session they abandoned.
     *
     * A Stripe session keeps its hosted URL for as long as it is `open`, so a buyer who closed the
     * tab can walk straight back into the same one. Anything else — already paid, or expired past
     * its `expires_at` — is null, and the caller opens a fresh session instead.
     */
    public function resumePaymentSession(StorePayment $payment): ?StorePaymentSessionData
    {
        if (! $payment->gateway_session_id) {
            return null;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($payment->gateway_session_id);
        } catch (\Throwable $e) {
            Log::warning('Could not reopen a Stripe session.', [
                'payment_id' => $payment->id,
                'reason' => $e->getMessage(),
            ]);

            return null;
        }

        if (($session->status ?? null) !== 'open' || blank($session->url ?? null)) {
            return null;
        }

        return new StorePaymentSessionData(
            redirectUrl: $session->url,
            sessionId: $session->id,
            raw: ['expires_at' => $session->expires_at ?? null],
        );
    }

    /**
     * Expire the session at Stripe so it can never be paid.
     *
     * This is what makes switching gateway safe. Without it a buyer could pay the abandoned Stripe
     * session after settling up elsewhere, and Stripe would capture money against an order that is
     * already paid — which markPaid() refuses to credit.
     */
    public function abandonPaymentSession(StorePayment $payment): void
    {
        if (! $payment->gateway_session_id) {
            return;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($payment->gateway_session_id);

            // Only an open session can be expired; Stripe errors on any other status.
            if (($session->status ?? null) === 'open') {
                $this->client()->checkout->sessions->expire($payment->gateway_session_id);
            }
        } catch (\Throwable $e) {
            // Never fatal: the session expires on its own, and blocking the buyer from paying by
            // another means would be the worse outcome.
            Log::warning('Could not expire an abandoned Stripe session.', [
                'payment_id' => $payment->id,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify against the raw request body. Nothing may have parsed or re-encoded it first:
     * the signature covers the exact bytes Stripe sent, so `$request->all()` would not match.
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = $this->credential('webhook_secret');
        $header = $request->header('Stripe-Signature');

        if (blank($secret) || blank($header)) {
            return false;
        }

        try {
            return WebhookSignature::verifyHeader(
                $request->getContent(),
                $header,
                $secret,
                self::SIGNATURE_TOLERANCE,
            );
        } catch (\Throwable $e) {
            Log::warning('Rejected a Stripe webhook signature.', ['reason' => $e->getMessage()]);

            return false;
        }
    }

    public function parseWebhook(Request $request): StoreGatewayEventData
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $eventId = (string) data_get($payload, 'id', '');
        $type = (string) data_get($payload, 'type', '');
        $object = (array) data_get($payload, 'data.object', []);

        return match ($type) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => $this->fromSession($eventId, $type, $object, $payload),

            'checkout.session.async_payment_failed' => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_FAILED,
                eventType: $type,
                sessionId: data_get($object, 'id'),
                orderUuid: data_get($object, 'client_reference_id') ?: data_get($object, 'metadata.order_uuid'),
                failureReason: __('The payment was declined by the bank.'),
                raw: $payload,
            ),

            'checkout.session.expired' => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_EXPIRED,
                eventType: $type,
                sessionId: data_get($object, 'id'),
                orderUuid: data_get($object, 'client_reference_id') ?: data_get($object, 'metadata.order_uuid'),
                failureReason: __('The payment session expired before it was completed.'),
                raw: $payload,
            ),

            'charge.refunded' => $this->fromRefundedCharge($eventId, $type, $object, $payload),

            'charge.dispute.created' => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_CHARGEBACK,
                eventType: $type,
                // The payment intent, not the charge: that is what was stored when the order was
                // marked paid, so it is what the payment can be looked up by.
                transactionId: $this->paymentIntentId(data_get($object, 'payment_intent')),
                amountMinor: (int) data_get($object, 'amount', 0),
                currency: strtoupper((string) data_get($object, 'currency', '')),
                refundId: data_get($object, 'id'),
                raw: $payload,
            ),

            default => new StoreGatewayEventData(
                eventId: $eventId,
                kind: StoreGatewayEventData::KIND_IGNORED,
                eventType: $type,
                raw: $payload,
            ),
        };
    }

    /**
     * Ask Stripe directly when the buyer lands back on the result page.
     *
     * Not a substitute for the webhook — a buyer who closes the tab never returns — but it makes
     * the common path immediate instead of waiting on delivery, and it keeps the store working on
     * an install where webhooks have not been wired up yet.
     */
    public function confirmOnReturn(StorePayment $payment): ?StoreGatewayEventData
    {
        if (! $payment->gateway_session_id) {
            return null;
        }

        try {
            $session = $this->client()->checkout->sessions->retrieve($payment->gateway_session_id);
        } catch (\Throwable $e) {
            Log::warning('Could not confirm a Stripe session on return.', [
                'payment_id' => $payment->id,
                'reason' => $e->getMessage(),
            ]);

            return null;
        }

        if (($session->payment_status ?? null) !== 'paid') {
            return null;
        }

        return new StoreGatewayEventData(
            eventId: 'return_'.$session->id,
            kind: StoreGatewayEventData::KIND_COMPLETED,
            sessionId: $session->id,
            transactionId: $this->paymentIntentId($session->payment_intent ?? null),
            amountMinor: (int) ($session->amount_total ?? 0),
            currency: strtoupper((string) ($session->currency ?? '')),
            orderUuid: $session->client_reference_id ?? null,
        );
    }

    public function refund(StorePayment $payment, int $amountMinor, ?string $reason = null): string
    {
        if (! $payment->gateway_transaction_id) {
            throw new \RuntimeException(__('This payment has no Stripe transaction to refund.'));
        }

        $refund = $this->client()->refunds->create(array_filter([
            'payment_intent' => $payment->gateway_transaction_id,
            'amount' => $amountMinor,
            'metadata' => array_filter(['reason' => $reason]),
        ]));

        return $refund->id;
    }

    /**
     * A completed Checkout Session. `payment_status` matters: with asynchronous methods the
     * session completes while the money is still in flight, and only the later
     * async_payment_succeeded event means it actually arrived.
     */
    private function fromSession(string $eventId, string $type, array $object, array $payload): StoreGatewayEventData
    {
        $paid = in_array(data_get($object, 'payment_status'), ['paid', 'no_payment_required'], true);

        if (! $paid) {
            return new StoreGatewayEventData(
                eventId: $eventId,
                eventType: $type,
                kind: StoreGatewayEventData::KIND_IGNORED,
                sessionId: data_get($object, 'id'),
                raw: $payload,
            );
        }

        return new StoreGatewayEventData(
            eventId: $eventId,
            eventType: $type,
            kind: StoreGatewayEventData::KIND_COMPLETED,
            sessionId: data_get($object, 'id'),
            transactionId: $this->paymentIntentId(data_get($object, 'payment_intent')),
            amountMinor: (int) data_get($object, 'amount_total', 0),
            currency: strtoupper((string) data_get($object, 'currency', '')),
            orderUuid: data_get($object, 'client_reference_id') ?: data_get($object, 'metadata.order_uuid'),
            raw: $payload,
        );
    }

    /**
     * `charge.refunded` carries the charge, whose `amount_refunded` is the running total rather
     * than the size of this refund. The individual refund object is what the store needs, both for
     * the delta and for an idempotency key, so take the newest entry off the charge's refund list
     * and only fall back to the cumulative figure if Stripe omitted it.
     */
    private function fromRefundedCharge(string $eventId, string $type, array $object, array $payload): StoreGatewayEventData
    {
        $latest = data_get($object, 'refunds.data.0');

        return new StoreGatewayEventData(
            eventId: $eventId,
            eventType: $type,
            kind: StoreGatewayEventData::KIND_REFUNDED,
            transactionId: $this->paymentIntentId(data_get($object, 'payment_intent')),
            amountMinor: (int) ($latest['amount'] ?? data_get($object, 'amount_refunded', 0)),
            currency: strtoupper((string) data_get($object, 'currency', '')),
            refundId: $latest['id'] ?? null,
            raw: $payload,
        );
    }

    /**
     * `payment_intent` arrives as a bare id normally, or as an expanded object on some events.
     */
    private function paymentIntentId(mixed $intent): ?string
    {
        if (is_string($intent)) {
            return $intent;
        }

        return data_get($intent, 'id');
    }

    /**
     * Stripe rejects three-decimal amounts that are not a multiple of ten minor units. Failing
     * loudly here beats an opaque API error at the moment a buyer clicks pay: it is a pricing
     * misconfiguration the admin has to fix, not something to silently round somebody's money to.
     */
    private function assertChargeable(int $amountMinor, string $currency): void
    {
        if ($this->currencies->exponentFor($currency) === 3 && $amountMinor % 10 !== 0) {
            throw new \RuntimeException(__('Stripe requires :currency amounts to end in a zero. Adjust the price or the currency rounding rule.', [
                'currency' => strtoupper($currency),
            ]));
        }
    }

    /**
     * Checkout Sessions must expire between 30 minutes and 24 hours out, so the store's own
     * pending-order TTL is clamped into that window rather than rejected by Stripe.
     */
    private function expiresAt(): int
    {
        $hours = min(max((int) config('store.pending_order_ttl_hours', 24), 1), 24);

        return now()->addHours($hours)->timestamp;
    }

    private function productName(StoreOrder $order): string
    {
        $site = $this->general->site_name ?: config('app.name');

        return $site.' — '.__('Order :number', ['number' => strtoupper(substr($order->uuid, 0, 8))]);
    }

    private function productDescription(StoreOrder $order): string
    {
        $names = $order->loadMissing('items')->items->map(
            fn ($item) => $item->quantity > 1 ? "{$item->quantity} x {$item->package_name}" : $item->package_name
        );

        $summary = $names->take(4)->implode(', ');

        if ($names->count() > 4) {
            $summary .= ', '.__(':count more', ['count' => $names->count() - 4]);
        }

        return $summary !== '' ? $summary : __('Store purchase');
    }

    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient([
            'api_key' => (string) $this->credential('secret_key'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Contracts\StorePaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Models\StoreGatewayWebhook;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Services\StoreOrderService;
use App\Utils\Payments\Data\StoreGatewayEventData;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The single inbound endpoint for every payment gateway.
 *
 * One dynamic {gateway} route rather than one route per driver, so adding a gateway needs no route
 * edit. It sits outside the auth.api-key group deliberately: a gateway cannot compute MineTrax's
 * own HMAC, so authenticity comes from the driver verifying the vendor's signature over the raw
 * request body instead.
 */
class ApiStoreWebhookController extends Controller
{
    public function __construct(
        private StorePaymentGatewayManager $gateways,
        private StoreOrderService $orders,
    ) {}

    public function handle(Request $request, string $gateway): JsonResponse
    {
        if (! config('store.enabled')) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $driver = $this->gateways->driver($gateway);

        if (! $driver || ! $driver->isEnabled()) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        // Signature first, before anything in the payload is trusted or even parsed.
        if (! $driver->verifyWebhook($request)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $event = $driver->parseWebhook($request);

        if ($event->eventId === '') {
            return response()->json(['message' => 'Unrecognised payload.'], 400);
        }

        // Claiming the event id is the replay guard, and it has to be a write rather than a read:
        // gateways retry the moment a response is slow, so two deliveries can be in flight at
        // once and only the unique index can arbitrate between them.
        try {
            $record = StoreGatewayWebhook::create([
                'gateway' => $gateway,
                'event_id' => $event->eventId,
                'type' => $event->eventType,
                'payload' => $event->raw ?: null,
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Already processed.']);
        }

        try {
            $this->applyEvent($driver, $event);
            $record->update(['processed_at' => now()]);
        } catch (\Throwable $e) {
            // Recorded, not rethrown: a 500 makes the gateway retry an event already claimed by
            // the replay guard, which would then be swallowed as a duplicate and never seen again.
            Log::error('Store webhook handling failed.', [
                'gateway' => $gateway,
                'event_id' => $event->eventId,
                'exception' => $e,
            ]);

            $record->update(['error' => substr($e->getMessage(), 0, 255)]);
        }

        return response()->json(['message' => 'ok']);
    }

    private function applyEvent(StorePaymentGatewayContract $driver, StoreGatewayEventData $event): void
    {
        $payment = $this->resolvePayment($driver, $event);

        if (! $payment) {
            Log::warning('Store webhook matched no payment.', [
                'gateway' => $driver->gateway()->value,
                'event_id' => $event->eventId,
                'kind' => $event->kind,
            ]);

            return;
        }

        match ($event->kind) {
            StoreGatewayEventData::KIND_COMPLETED => $this->orders->markPaid(
                $payment->order,
                $payment,
                $event->amountMinor,
                $event->currency,
                $event->transactionId,
            ),

            StoreGatewayEventData::KIND_FAILED => $this->orders->failPaymentAttempt(
                $payment,
                $event->failureReason ?? __('The gateway reported the payment failed.'),
            ),

            // The buyer never paid, so the order releases everything it was holding.
            StoreGatewayEventData::KIND_EXPIRED => $this->expire($payment, $event),

            StoreGatewayEventData::KIND_REFUNDED => $this->orders->recordRefund(
                payment: $payment,
                amountMinor: (int) $event->amountMinor,
                gatewayRefundId: $event->refundId,
                reason: __('Refunded at the gateway.'),
                payload: $event->raw,
            ),

            StoreGatewayEventData::KIND_CHARGEBACK => $this->orders->recordRefund(
                payment: $payment,
                amountMinor: (int) $event->amountMinor,
                isChargeback: true,
                gatewayRefundId: $event->refundId,
                reason: __('Payment disputed by the cardholder.'),
                payload: $event->raw,
            ),

            default => null,
        };
    }

    private function expire(StorePayment $payment, StoreGatewayEventData $event): void
    {
        $this->orders->failPaymentAttempt($payment, $event->failureReason ?? __('The payment session expired.'));
        $this->orders->cancel($payment->order, __('The payment session expired before it was completed.'));
    }

    /**
     * Find the charge attempt an event belongs to.
     *
     * Three routes in, because different vendors echo back different things: the checkout session
     * id, the transaction id once a charge exists, or the order uuid carried in metadata.
     */
    private function resolvePayment(StorePaymentGatewayContract $driver, StoreGatewayEventData $event): ?StorePayment
    {
        $gateway = $driver->gateway()->value;

        if ($event->sessionId) {
            $payment = StorePayment::with('order')
                ->where('gateway', $gateway)
                ->where('gateway_session_id', $event->sessionId)
                ->first();

            if ($payment) {
                return $payment;
            }
        }

        if ($event->transactionId) {
            $payment = StorePayment::with('order')
                ->where('gateway', $gateway)
                ->where('gateway_transaction_id', $event->transactionId)
                ->first();

            if ($payment) {
                return $payment;
            }
        }

        if ($event->orderUuid) {
            $order = StoreOrder::where('uuid', $event->orderUuid)->first();

            $payment = $order?->payments()->where('gateway', $gateway)->latest('id')->first();

            if ($payment) {
                $payment->setRelation('order', $order);

                return $payment;
            }
        }

        return null;
    }
}

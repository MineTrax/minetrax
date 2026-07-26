<?php

namespace App\Services;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreGiftCardTransactionType;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderCancelled;
use App\Events\StoreOrderCompleted;
use App\Events\StoreOrderPaid;
use App\Events\StoreOrderRefunded;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Every order state transition.
 *
 * Each method locks the order row, re-reads its status inside the transaction, and checks the
 * transition against StoreOrderStatus. That combination is what makes the whole thing idempotent:
 * gateways retry webhooks aggressively and will happily deliver the same event twice, so a second
 * markPaid() must be a no-op rather than a second delivery.
 */
class StoreOrderService
{
    /**
     * PENDING -> PAID.
     *
     * The paid amount and currency are verified against amount_due first. A mismatch fails the
     * payment and leaves the order untouched, because a partial or wrong-currency capture is a
     * problem for a human, not something to deliver against.
     */
    public function markPaid(
        StoreOrder $order,
        StorePayment $payment,
        ?int $paidAmountMinor = null,
        ?string $paidCurrency = null,
        ?string $transactionId = null,
    ): bool {
        return DB::transaction(function () use ($order, $payment, $paidAmountMinor, $paidCurrency, $transactionId) {
            $order = StoreOrder::lockForUpdate()->find($order->id);

            if (! $order->status->canTransitionTo(StoreOrderStatus::PAID)) {
                // Already paid or already finished. A webhook replay lands here.
                return false;
            }

            if ($paidAmountMinor !== null && $paidAmountMinor !== (int) $order->amount_due) {
                $this->failPayment($payment, "Amount mismatch: expected {$order->amount_due}, received {$paidAmountMinor}");

                return false;
            }

            if ($paidCurrency !== null && strtoupper($paidCurrency) !== strtoupper($order->currency)) {
                $this->failPayment($payment, "Currency mismatch: expected {$order->currency}, received {$paidCurrency}");

                return false;
            }

            $payment->update([
                'status' => StorePaymentStatus::COMPLETED,
                'gateway_transaction_id' => $transactionId ?? $payment->gateway_transaction_id,
                'paid_at' => now(),
            ]);

            $order->update([
                'status' => StoreOrderStatus::PAID,
                'gateway' => $payment->gateway,
                'paid_at' => now(),
            ]);

            $this->redeemGiftCard($order);

            event(new StoreOrderPaid($order->fresh()));

            return true;
        });
    }

    /**
     * PAID -> COMPLETED, once delivery has been enqueued.
     */
    public function markCompleted(StoreOrder $order, StoreDeliveryStatus $deliveryStatus): bool
    {
        return DB::transaction(function () use ($order, $deliveryStatus) {
            $order = StoreOrder::lockForUpdate()->find($order->id);

            if (! $order->status->canTransitionTo(StoreOrderStatus::COMPLETED)) {
                return false;
            }

            $order->update([
                'status' => StoreOrderStatus::COMPLETED,
                'delivery_status' => $deliveryStatus,
                'completed_at' => now(),
            ]);

            event(new StoreOrderCompleted($order->fresh()));

            return true;
        });
    }

    /**
     * Cancel an unpaid or unfulfilled order, releasing everything it reserved.
     */
    public function cancel(StoreOrder $order, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($order, $reason) {
            $order = StoreOrder::lockForUpdate()->find($order->id);

            if (! $order->status->canTransitionTo(StoreOrderStatus::CANCELLED)) {
                return false;
            }

            $this->releaseCoupon($order);
            $this->refundGiftCard($order, 'Order cancelled');

            $order->update([
                'status' => StoreOrderStatus::CANCELLED,
                'cancelled_at' => now(),
                'notes' => $reason ? trim($order->notes."\n".$reason) : $order->notes,
            ]);

            event(new StoreOrderCancelled($order->fresh()));

            return true;
        });
    }

    /**
     * Record a refund. Partial refunds leave grants active; a full refund revokes them.
     */
    public function refund(StoreOrder $order, int $amountMinor, bool $isChargeback = false): bool
    {
        return DB::transaction(function () use ($order, $amountMinor, $isChargeback) {
            $order = StoreOrder::lockForUpdate()->find($order->id);

            $alreadyRefunded = (int) $order->payments()->sum('refunded_amount');
            $isFull = $isChargeback || ($alreadyRefunded + $amountMinor) >= (int) $order->amount_due;

            $target = match (true) {
                $isChargeback => StoreOrderStatus::CHARGEBACK,
                $isFull => StoreOrderStatus::REFUNDED,
                default => StoreOrderStatus::PARTIALLY_REFUNDED,
            };

            if (! $order->status->canTransitionTo($target)) {
                return false;
            }

            $order->update([
                'status' => $target,
                'refunded_at' => now(),
            ]);

            if ($target->isRevoking()) {
                $this->revokeGrants($order);
            }

            event(new StoreOrderRefunded($order->fresh(), $isChargeback));

            return true;
        });
    }

    /**
     * Coupon usage is reserved when the order is created, so cancelling must give it back.
     *
     * Refunds deliberately do NOT release it: the code was genuinely used, and releasing it would
     * let a buyer farm a limited coupon by ordering and refunding.
     */
    private function releaseCoupon(StoreOrder $order): void
    {
        if (! $order->store_coupon_id) {
            return;
        }

        $order->coupon()->lockForUpdate()->first()?->decrement('used_count');
    }

    /**
     * Debit the gift card at the point the order is actually paid.
     */
    private function redeemGiftCard(StoreOrder $order): void
    {
        if (! $order->store_gift_card_id || $order->gift_card_amount <= 0) {
            return;
        }

        /** @var StoreGiftCard|null $card */
        $card = StoreGiftCard::lockForUpdate()->find($order->store_gift_card_id);

        if (! $card) {
            return;
        }

        // Already recorded (webhook replay that got past the status guard somehow).
        if ($card->transactions()->where('store_order_id', $order->id)->where('type', StoreGiftCardTransactionType::REDEEM)->exists()) {
            return;
        }

        $amount = min((int) $card->balance, (int) $order->gift_card_amount);
        $balanceAfter = (int) $card->balance - $amount;

        $card->update(['balance' => $balanceAfter]);
        $card->transactions()->create([
            'store_order_id' => $order->id,
            'type' => StoreGiftCardTransactionType::REDEEM,
            'amount' => -$amount,
            'balance_after' => $balanceAfter,
        ]);
    }

    /**
     * Give gift card balance back when an order is cancelled before it was ever paid for.
     */
    private function refundGiftCard(StoreOrder $order, string $note): void
    {
        if (! $order->store_gift_card_id || $order->gift_card_amount <= 0) {
            return;
        }

        /** @var StoreGiftCard|null $card */
        $card = StoreGiftCard::lockForUpdate()->find($order->store_gift_card_id);

        if (! $card) {
            return;
        }

        $redeemed = (int) abs($card->transactions()
            ->where('store_order_id', $order->id)
            ->where('type', StoreGiftCardTransactionType::REDEEM)
            ->sum('amount'));

        if ($redeemed <= 0) {
            return;
        }

        if ($card->transactions()->where('store_order_id', $order->id)->where('type', StoreGiftCardTransactionType::REVERSAL)->exists()) {
            return;
        }

        $balanceAfter = (int) $card->balance + $redeemed;
        $card->update(['balance' => $balanceAfter]);
        $card->transactions()->create([
            'store_order_id' => $order->id,
            'type' => StoreGiftCardTransactionType::REVERSAL,
            'amount' => $redeemed,
            'balance_after' => $balanceAfter,
            'note' => $note,
        ]);
    }

    private function revokeGrants(StoreOrder $order): void
    {
        foreach ($order->items as $item) {
            $item->grant()
                ->where('status', StorePackageGrantStatus::ACTIVE)
                ->update([
                    'status' => StorePackageGrantStatus::REVOKED,
                    'revoked_at' => now(),
                ]);
        }
    }

    private function failPayment(StorePayment $payment, string $reason): void
    {
        Log::warning('Store payment rejected: '.$reason, ['payment_id' => $payment->id]);

        $payment->update([
            'status' => StorePaymentStatus::FAILED,
            'failure_reason' => $reason,
        ]);
    }
}

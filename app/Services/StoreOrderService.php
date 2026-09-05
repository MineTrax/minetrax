<?php

namespace App\Services;

use App\Enums\CommandQueueStatus;
use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreGiftCardTransactionType;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentRefundType;
use App\Enums\StorePaymentStatus;
use App\Events\StoreOrderCancelled;
use App\Events\StoreOrderCompleted;
use App\Events\StoreOrderPaid;
use App\Events\StoreOrderRefunded;
use App\Events\StorePaymentFailed;
use App\Models\StoreCoupon;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use App\Models\StorePaymentRefund;
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
     * Store history goes in its own activity log, so a retention policy or a purge on the store's
     * own audit trail never touches anybody else's.
     */
    public const ACTIVITY_LOG = 'store';

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

            // Inside the same locked transaction as the transition, which is what makes it safe: a
            // replayed webhook never reaches here, because canTransitionTo() has already turned it
            // away above. A listener on StoreOrderPaid would be a second thing to keep idempotent
            // for no gain.
            $this->applyReferralEarning($order, refundedTotal: 0);

            $this->record($order, 'paid', __('Payment received'), [
                'gateway' => $payment->gateway?->value,
                'amount' => (int) $payment->amount,
                'currency' => $payment->currency,
                'transaction_id' => $payment->gateway_transaction_id,
            ]);

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

            $this->record($order, 'completed', __('Delivery queued'), [
                'delivery_status' => $deliveryStatus->value,
            ]);

            event(new StoreOrderCompleted($order->fresh()));

            return true;
        });
    }

    /**
     * Re-derive delivery_status from how the order's purchase commands actually fared.
     *
     * markCompleted() records that delivery was queued; this records whether it landed. Run each
     * time RunCommandQueueJob settles one of the order's rows and after an admin re-send, so the
     * buyer's result page and the admin list follow the queue instead of freezing on PENDING.
     *
     * Only purchase deliveries count. Refund and expiry commands write rows against the same
     * order, and a revocation that fails must not make a delivered order look undelivered.
     *
     * Locked like every other transition: two rows of the same order can settle on two workers at
     * once, and without the lock the slower one could overwrite DELIVERED with a stale PENDING.
     */
    public function syncDeliveryStatus(StoreOrder $order): StoreDeliveryStatus
    {
        return DB::transaction(function () use ($order) {
            $order = StoreOrder::lockForUpdate()->find($order->id);

            $summary = $this->deliverySummary($order);

            if ($summary['total'] === 0) {
                // Nothing was ever queued, so the queue has nothing to say about it.
                return $order->delivery_status;
            }

            $status = match (true) {
                $summary['in_progress'] > 0 => StoreDeliveryStatus::PENDING,
                $summary['failed'] === 0 => StoreDeliveryStatus::DELIVERED,
                $summary['completed'] > 0 => StoreDeliveryStatus::PARTIAL,
                default => StoreDeliveryStatus::FAILED,
            };

            if ($status === $order->delivery_status) {
                return $status;
            }

            $order->update(['delivery_status' => $status]);

            // A line for each outcome, not for each command: three rows settling one after
            // another is one "delivered" in the timeline, and a re-send that puts it back to
            // PENDING is already recorded by whoever pressed the button.
            if ($status !== StoreDeliveryStatus::PENDING) {
                $this->record($order, 'delivery_'.$status->value, match ($status) {
                    StoreDeliveryStatus::DELIVERED => __('Delivered to the server'),
                    StoreDeliveryStatus::PARTIAL => __('Delivery partly failed'),
                    StoreDeliveryStatus::FAILED => __('Delivery failed'),
                }, [
                    'completed' => $summary['completed'],
                    'failed' => $summary['failed'],
                    'total' => $summary['total'],
                ]);
            }

            return $status;
        });
    }

    /**
     * How the order's purchase commands stand on the queue.
     *
     * A FAILED row with attempts left is still in progress: the every-minute sweeper will retry it,
     * and telling the buyer it failed a minute before it succeeds helps nobody. DEFERRED is in
     * progress too, but flagged, because "join the server" is something the buyer can act on and
     * "delivering" is not.
     *
     * @return array{total: int, completed: int, in_progress: int, failed: int, waiting_for_player: bool}
     */
    public function deliverySummary(StoreOrder $order): array
    {
        $deliveries = $order->deliveries()
            ->where('trigger', StoreCommandTrigger::PURCHASE)
            ->with('commandQueue:id,status,attempts,max_attempts')
            ->get();

        $summary = [
            'total' => $deliveries->count(),
            'completed' => 0,
            'in_progress' => 0,
            'failed' => 0,
            'waiting_for_player' => false,
        ];

        foreach ($deliveries as $delivery) {
            $queue = $delivery->commandQueue;
            $status = $queue?->status;

            if ($status === CommandQueueStatus::COMPLETED) {
                $summary['completed']++;
            } elseif ($status === CommandQueueStatus::DEFERRED) {
                $summary['in_progress']++;
                $summary['waiting_for_player'] = true;
            } elseif (in_array($status, [CommandQueueStatus::PENDING, CommandQueueStatus::RUNNING], true)) {
                $summary['in_progress']++;
            } elseif ($status === CommandQueueStatus::FAILED && (int) $queue->attempts < (int) $queue->max_attempts) {
                $summary['in_progress']++;
            } else {
                // Out of attempts, cancelled for want of a webquery port, or the queue row is gone.
                $summary['failed']++;
            }
        }

        return $summary;
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

            $this->releaseCoupons($order);
            $this->refundGiftCard($order, 'Order cancelled');

            $order->update([
                'status' => StoreOrderStatus::CANCELLED,
                'cancelled_at' => now(),
                'notes' => $reason ? trim($order->notes."\n".$reason) : $order->notes,
            ]);

            $this->record($order, 'cancelled', __('Order cancelled'), [
                'reason' => $reason,
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

            // A chargeback takes the whole payment back however much was named on it, so it owes
            // nothing regardless of the figure the gateway reported.
            $this->applyReferralEarning($order, refundedTotal: $isChargeback
                ? (int) $order->amount_due
                : $alreadyRefunded + $amountMinor);

            $this->record(
                $order,
                $isChargeback ? 'chargeback' : ($isFull ? 'refunded' : 'partially_refunded'),
                $isChargeback ? __('Payment disputed and reversed') : __('Refund issued'),
                [
                    'amount' => $amountMinor,
                    'currency' => $order->currency,
                    'grants_revoked' => $target->isRevoking(),
                ]
            );

            event(new StoreOrderRefunded($order->fresh(), $isChargeback, $amountMinor));

            return true;
        });
    }

    /**
     * Record a refund against a specific payment, then transition the order.
     *
     * Separate from refund() because the transition is about the order while the ledger is about
     * the charge: an admin-issued refund, a gateway webhook and a dispute all produce the same
     * order transition but different paperwork.
     *
     * Idempotent on the gateway's own refund id, so a redelivered refund webhook records nothing
     * a second time. The amount is clamped to what is actually left unrefunded on the payment,
     * which also protects against a gateway that reports a cumulative figure instead of a delta.
     */
    public function recordRefund(
        StorePayment $payment,
        int $amountMinor,
        bool $isChargeback = false,
        ?string $gatewayRefundId = null,
        ?string $reason = null,
        ?int $createdBy = null,
        array $payload = [],
    ): bool {
        if ($gatewayRefundId && StorePaymentRefund::where('gateway_refund_id', $gatewayRefundId)->exists()) {
            return false;
        }

        $remaining = (int) $payment->amount - (int) $payment->refunded_amount;
        $amountMinor = max(0, min($amountMinor, $remaining));

        if ($amountMinor <= 0 && ! $isChargeback) {
            return false;
        }

        $order = $payment->order;

        // Transition first: refund() derives full-vs-partial from the refunded totals as they
        // stand now, so incrementing before it ran would make every refund look like a full one.
        if (! $this->refund($order, $amountMinor, $isChargeback)) {
            return false;
        }

        DB::transaction(function () use ($payment, $amountMinor, $isChargeback, $gatewayRefundId, $reason, $createdBy, $payload) {
            $payment->refunds()->create([
                'type' => $isChargeback ? StorePaymentRefundType::CHARGEBACK : StorePaymentRefundType::REFUND,
                'gateway_refund_id' => $gatewayRefundId,
                'amount' => $amountMinor,
                'currency' => $payment->currency,
                'reason' => $reason,
                'payload' => $payload ?: null,
                'created_by' => $createdBy,
            ]);

            $refunded = (int) $payment->refunded_amount + $amountMinor;

            $payment->update([
                'refunded_amount' => $refunded,
                'status' => match (true) {
                    $isChargeback => StorePaymentStatus::CHARGEBACK,
                    $refunded >= (int) $payment->amount => StorePaymentStatus::REFUNDED,
                    default => StorePaymentStatus::PARTIALLY_REFUNDED,
                },
            ]);
        });

        return true;
    }

    /**
     * Mark a charge attempt failed without touching the order, which stays PENDING so the buyer
     * can retry with another method.
     */
    public function failPaymentAttempt(StorePayment $payment, string $reason): void
    {
        $this->failPayment($payment, $reason);
    }

    /**
     * A fresh payment row for another attempt at an order that is still pending.
     *
     * The amount is re-read from the order rather than copied off the previous attempt, so a figure
     * that moved between attempts cannot be charged at yesterday's value.
     */
    public function startPaymentAttempt(StoreOrder $order, string $gateway): StorePayment
    {
        return $order->payments()->create([
            'gateway' => $gateway,
            'status' => StorePaymentStatus::PENDING,
            'amount' => $order->amount_due,
            'currency' => $order->currency,
        ]);
    }

    /**
     * Work out what this order owes its referrer, and write it to the order.
     *
     * Always recomputed from the order's own snapshots rather than adjusted in place, so several
     * partial refunds cannot drift: each one recalculates from `referral_share_bp` and the totals
     * that were frozen at checkout, and arrives at the same answer whatever order they land in.
     *
     * The share is taken on **net goods** — the total less tax. Tax is the government's money, not
     * revenue, and paying commission on it would mean the store owed more than it kept.
     *
     * @param  int  $refundedTotal  cumulative refunded against amount_due, including the refund
     *                              currently being recorded
     */
    private function applyReferralEarning(StoreOrder $order, int $refundedTotal): void
    {
        if (! $order->store_referral_id) {
            return;
        }

        $netGoods = max(0, (int) $order->total - (int) $order->tax_amount);
        $earning = intdiv($netGoods * (int) $order->referral_share_bp, 10000);

        $due = (int) $order->amount_due;

        if ($refundedTotal > 0 && $due > 0) {
            // Scaled by what is left unrefunded. An order paid entirely by gift card has nothing
            // refundable at the gateway, so there is no fraction to take and the sale stands.
            $earning = intdiv($earning * max(0, $due - $refundedTotal), $due);
        }

        $order->update([
            'referral_earning' => $earning,
            // Converted through the order's own ratio rather than today's exchange rate. Exponent
            // free, and it cannot rewrite history the way re-converting at report time would — the
            // same idiom StoreStatisticsController uses to prorate a line's share of an order.
            'referral_earning_base' => (int) $order->total > 0
                ? intdiv($earning * (int) $order->base_total, (int) $order->total)
                : 0,
        ]);
    }

    /**
     * Hand back the use each of the order's coupons reserved at checkout.
     *
     * Usage is reserved when the order is created, so cancelling must give it back. Refunds
     * deliberately do NOT release it: the code was genuinely used, and releasing it would let a
     * buyer farm a limited coupon by ordering and refunding.
     *
     * Ascending id order, matching StoreCheckoutService::reserveCoupons() — a cancel racing a
     * checkout over the same pair of coupons must take them the same way round or the two deadlock.
     */
    private function releaseCoupons(StoreOrder $order): void
    {
        $couponIds = $order->coupons()
            ->whereNotNull('store_coupon_id')
            ->orderBy('store_coupon_id')
            ->pluck('store_coupon_id');

        foreach ($couponIds as $couponId) {
            StoreCoupon::lockForUpdate()->find($couponId)?->decrement('used_count');
        }
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
            $revoked = $item->grant()
                ->where('status', StorePackageGrantStatus::ACTIVE)
                ->update([
                    'status' => StorePackageGrantStatus::REVOKED,
                    'revoked_at' => now(),
                ]);

            // Give the stock back. The purchase limits are counted from paid-state orders, so a
            // refunded one no longer consumes an allowance; sold_count has to follow or the
            // storefront would keep showing a package as sold out that checkout would happily sell.
            if ($revoked) {
                $item->package?->decrement('sold_count', min((int) $item->quantity, (int) $item->package->sold_count));
            }
        }
    }

    private function failPayment(StorePayment $payment, string $reason): void
    {
        Log::warning('Store payment rejected: '.$reason, ['payment_id' => $payment->id]);

        $payment->update([
            'status' => StorePaymentStatus::FAILED,
            'failure_reason' => $reason,
        ]);

        if ($payment->order) {
            $this->record($payment->order, 'payment_failed', __('Payment attempt failed'), [
                'gateway' => $payment->gateway?->value,
                'reason' => $reason,
            ]);
        }

        event(new StorePaymentFailed($payment->fresh(), $reason));
    }

    /**
     * Write one line of the order's history.
     *
     * The causer is whoever is signed in, which is the point of the whole thing: a dispute weeks
     * later needs to say who marked an order paid or who refunded it. A null causer is not a gap —
     * it is a webhook or a scheduled sweep, i.e. nobody, and that reads correctly in the timeline.
     *
     * Logged explicitly rather than through model events. Automatic attribute diffs would fill the
     * timeline with `updated_at` noise and say nothing about what actually happened.
     *
     * @param  array<string, mixed>  $properties
     */
    private function record(StoreOrder $order, string $event, string $description, array $properties = []): void
    {
        activity(self::ACTIVITY_LOG)
            ->performedOn($order)
            ->causedBy(auth()->user())
            ->event($event)
            ->withProperties(array_filter(
                $properties,
                fn ($value) => $value !== null && $value !== ''
            ))
            ->log($description);
    }
}

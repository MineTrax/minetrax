<?php

namespace App\Listeners;

use App\Events\StoreOrderRefunded;
use App\Notifications\StoreOrderRefundedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Tells the buyer about a refund.
 *
 * Silent on chargebacks: the buyer raised that one with their own bank, so an email explaining it
 * to them tells them nothing and reads as an accusation. Staff get told instead.
 */
class SendStoreOrderRefundNotice implements ShouldQueue
{
    public function handle(StoreOrderRefunded $event): void
    {
        if ($event->isChargeback) {
            return;
        }

        $order = $event->order;
        $amount = $event->amountMinor > 0 ? $event->amountMinor : (int) $order->total;

        if ($order->user) {
            $order->user->notify(new StoreOrderRefundedNotification($order, $amount));

            return;
        }

        // A guest has no account to notify, so the address they gave at checkout is the only route
        // there is. Nothing to send to if they were never asked for one.
        if ($order->email) {
            Notification::route('mail', $order->email)
                ->notify(new StoreOrderRefundedNotification($order, $amount));
        }
    }
}

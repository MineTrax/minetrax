<?php

namespace App\Listeners;

use App\Events\StorePaymentFailed;
use App\Notifications\StorePaymentFailedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Tells the buyer a charge was declined and the order is still payable.
 *
 * Worth sending because the failure usually happens on the gateway's own page, which the buyer may
 * have closed before it said anything useful — the order otherwise just sits pending until the
 * stale sweep cancels it.
 */
class NotifyBuyerOnStorePaymentFailed implements ShouldQueue
{
    public function handle(StorePaymentFailed $event): void
    {
        $order = $event->payment->order;

        if (! $order) {
            return;
        }

        if ($order->user) {
            $order->user->notify(new StorePaymentFailedNotification($order));

            return;
        }

        if ($order->email) {
            Notification::route('mail', $order->email)
                ->notify(new StorePaymentFailedNotification($order));
        }
    }
}

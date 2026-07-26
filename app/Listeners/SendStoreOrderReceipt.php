<?php

namespace App\Listeners;

use App\Events\StoreOrderPaid;
use App\Notifications\StoreOrderPaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Sends the buyer their receipt.
 *
 * Fires on payment rather than on completion so the confirmation is immediate: delivery can sit
 * deferred for days waiting on an offline player, and a buyer should not be left wondering whether
 * their money went anywhere in the meantime.
 */
class SendStoreOrderReceipt implements ShouldQueue
{
    public function handle(StoreOrderPaid $event): void
    {
        $order = $event->order->loadMissing('items');

        if ($order->user) {
            $order->user->notify(new StoreOrderPaidNotification($order));

            return;
        }

        // A guest has no account to notify, so the address they gave at checkout is the only
        // route there is. Nothing to send to if they were not asked for one.
        if ($order->email) {
            Notification::route('mail', $order->email)
                ->notify(new StoreOrderPaidNotification($order));
        }
    }
}

<?php

namespace App\Listeners;

use App\Events\StoreOrderPaid;
use App\Models\User;
use App\Notifications\StoreOrderPlacedStaffNotification;
use App\Settings\StoreSettings;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Fans a new sale out to whoever can read store orders.
 *
 * Scoped by permission rather than by role, so an install that has invented its own staff roles
 * still notifies the right people.
 */
class NotifyStaffOnStoreOrderPaid implements ShouldQueue
{
    public function __construct(private StoreSettings $settings) {}

    public function handle(StoreOrderPaid $event): void
    {
        if (! $this->settings->notify_staff_on_purchase) {
            return;
        }

        $order = $event->order->loadMissing('items', 'user');

        User::permission('read store_orders')
            ->get()
            ->each
            ->notify(new StoreOrderPlacedStaffNotification($order));
    }
}

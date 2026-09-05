<?php

namespace App\Listeners;

use App\Events\CommandQueueRunFinished;
use App\Models\StoreOrderDelivery;
use App\Services\StoreOrderService;

/**
 * Keep an order's delivery_status in step with the command queue.
 *
 * markCompleted() only records that delivery was queued. Without this, every order with a real
 * command sat on PENDING forever, and the result page polled for a change that never came.
 */
class SyncStoreOrderDeliveryOnCommandRun
{
    public function __construct(private StoreOrderService $orders) {}

    public function handle(CommandQueueRunFinished $event): void
    {
        if ($event->commandQueue->tag !== 'store') {
            return;
        }

        $delivery = StoreOrderDelivery::with('order')
            ->where('command_queue_id', $event->commandQueue->id)
            ->first();

        if (! $delivery?->order) {
            return;
        }

        $this->orders->syncDeliveryStatus($delivery->order);
    }
}

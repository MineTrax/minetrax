<?php

namespace App\Listeners;

use App\Events\StoreOrderPaid;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;

class DispatchStoreOrderDeliveryOnPaid
{
    public function handle(StoreOrderPaid $event): void
    {
        ProcessStoreOrderPurchaseJob::dispatch($event->order);
    }
}

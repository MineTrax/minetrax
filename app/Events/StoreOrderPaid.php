<?php

namespace App\Events;

use App\Models\StoreOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreOrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public StoreOrder $order) {}
}

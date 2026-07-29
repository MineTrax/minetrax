<?php

namespace App\Events;

use App\Models\StoreOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreOrderRefunded
{
    use Dispatchable, SerializesModels;

    /**
     * @param  int  $amountMinor  What this refund returned, in the order's currency — not the order
     *                            total, which a partial refund does not touch. Carried on the event
     *                            because the ledger row is written after the transition.
     */
    public function __construct(
        public StoreOrder $order,
        public bool $isChargeback = false,
        public int $amountMinor = 0,
    ) {}
}

<?php

namespace App\Listeners;

use App\Enums\StorePackageCommandTrigger;
use App\Events\StoreOrderRefunded;
use App\Jobs\Store\ProcessStoreOrderRevocationJob;

class DispatchStoreOrderRevocationOnRefund
{
    /**
     * Queue the in-game revocation for a refund that actually took the purchase away.
     *
     * A partial refund does not: the buyer keeps what they bought and gets some money back, so its
     * grants stay active and nothing is taken off them. isRevoking() is the same test
     * StoreOrderService uses to decide whether to revoke the grants, so the two cannot disagree.
     */
    public function handle(StoreOrderRefunded $event): void
    {
        if (! $event->order->status->isRevoking()) {
            return;
        }

        ProcessStoreOrderRevocationJob::dispatch(
            $event->order,
            $event->isChargeback
                ? StorePackageCommandTrigger::CHARGEBACK
                : StorePackageCommandTrigger::REFUND,
        );
    }
}

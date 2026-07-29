<?php

namespace App\Jobs\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Models\StoreOrder;
use App\Services\StoreCommandDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs the commands that take a refunded or charged-back purchase back off the player.
 *
 * StoreOrderService::refund() already revoked the grants, which is the website's own record. This is
 * the other half: without it a refunded rank stays on the player in game, and the store has given
 * the money back and kept nothing.
 *
 * On the longtask queue for the same reason the purchase job is: a large order fans out across every
 * server, and a slow socket must not hold up the request that triggered it.
 */
class ProcessStoreOrderRevocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private StoreOrder $order,
        private StorePackageCommandTrigger $trigger,
    ) {
        $this->onQueue('longtask');
    }

    public function handle(StoreCommandDispatchService $dispatcher): void
    {
        $order = $this->order->fresh(['items.package.commands.servers']);

        if (! $order) {
            return;
        }

        // A retry costs nothing: the unique index on store_order_deliveries means a repeat of the
        // same (item, command, server, trigger) creates no second delivery.
        $dispatcher->dispatchForOrder($order, $this->trigger);
    }
}

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
 * On the default queue for the same reason the purchase job is: it writes rows and hands the socket
 * work to RunCommandQueueJob, so it is short, and taking a perk back off a refunded player should not
 * wait behind a multi-minute sweep.
 */
class ProcessStoreOrderRevocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private StoreOrder $order,
        private StorePackageCommandTrigger $trigger,
    ) {}

    public function handle(StoreCommandDispatchService $dispatcher): void
    {
        $order = $this->order->fresh([
            'items.package.commands.servers',
            // A sale's own refund and chargeback commands take back the bonus it granted, so they
            // have to come along even though the sale itself is long over.
            'items.sale.commands.servers',
            'items.sale.commands.packages',
        ]);

        if (! $order) {
            return;
        }

        // A retry costs nothing: the unique index on store_order_deliveries means a repeat of the
        // same (item, command, server, trigger) creates no second delivery.
        $dispatcher->dispatchForOrder($order, $this->trigger);
    }
}

<?php

namespace App\Jobs\Store;

use App\Enums\StoreCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Models\StoreOrder;
use App\Services\StoreCheckoutService;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreGiftCardService;
use App\Services\StoreOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fulfils a paid order: issue the grants, queue the purchase commands, complete the order.
 *
 * Deliberately on the default queue, not longtask. Nothing here touches a socket — it writes grants,
 * gift cards, command_queues and delivery rows, then hands each row to RunCommandQueueJob, which is
 * where the webquery round trip actually happens and which also runs on default. This job is short
 * database work, and the buyer is watching the result page while it runs, so it must not queue behind
 * the multi-minute sweeps that longtask exists for — a player recalculation over a few hundred
 * thousand rows would leave them staring at a spinner.
 */
class ProcessStoreOrderPurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private StoreOrder $order) {}

    public function handle(
        StoreCommandDispatchService $dispatcher,
        StoreOrderService $orders,
        StoreGiftCardService $giftCards,
    ): void {
        $order = $this->order->fresh(['items.package.commands', 'items.sale.commands']);

        if (! $order || ! $order->status->isPaidState()) {
            // Refunded or cancelled between payment and this job running.
            return;
        }

        $this->issueGrants($order);
        $this->issueGiftCards($order, $giftCards);

        $deliveryStatus = $dispatcher->dispatchForOrder($order, StoreCommandTrigger::PURCHASE);

        $orders->markCompleted($order, $deliveryStatus);
    }

    /**
     * One grant per order item. Grants are what the expiry sweep, the prerequisite check and the
     * cumulative-category upgrade credit read, so a permanent package still gets one, just without
     * an expiry. Purchase limits are not counted from grants: they sum paid-state order items, see
     * {@see StoreCheckoutService::assertPurchasable()}.
     *
     * sold_count is incremented here rather than in its own pass, tied to the grant actually
     * being created. That is what keeps stock consumption correct when this job is retried:
     * a bare increment would inflate it every attempt.
     */
    private function issueGrants(StoreOrder $order): void
    {
        foreach ($order->items as $item) {
            if ($item->grant()->exists()) {
                continue; // already fulfilled; this job was retried
            }

            $item->grant()->create([
                'store_package_id' => $item->store_package_id,
                'player_uuid' => $order->player_uuid,
                'status' => StorePackageGrantStatus::ACTIVE,
                'granted_at' => now(),
                'expires_at' => $item->expiry_duration_days
                    ? now()->addDays((int) $item->expiry_duration_days)
                    : null,
            ]);

            // Stock is only consumed once the order is genuinely paid, so an abandoned checkout
            // never holds inventory.
            $item->package?->increment('sold_count', (int) $item->quantity);
        }
    }

    /**
     * Mint store credit for any item whose package sells a gift card.
     *
     * Idempotent through store_order_items.store_gift_card_id, so a retry of this job hands the
     * buyer the same code rather than a second one.
     */
    private function issueGiftCards(StoreOrder $order, StoreGiftCardService $giftCards): void
    {
        foreach ($order->items as $item) {
            $giftCards->issueForOrderItem($order, $item);
        }
    }
}

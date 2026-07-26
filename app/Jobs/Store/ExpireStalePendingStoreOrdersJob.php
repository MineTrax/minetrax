<?php

namespace App\Jobs\Store;

use App\Enums\StoreOrderStatus;
use App\Models\StoreOrder;
use App\Services\StoreOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Cancels checkouts the buyer walked away from.
 *
 * A PENDING order is holding things: a use of a limited coupon, and possibly gift card balance.
 * Without this they are held forever by anyone who opens a checkout and closes the tab, so the
 * last use of a coupon could be locked away by an order that will never be paid.
 *
 * Cancellation goes through StoreOrderService so the coupon is released and the gift card
 * re-credited by the same code path an admin cancellation uses.
 */
class ExpireStalePendingStoreOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(StoreOrderService $orders): void
    {
        if (! config('store.enabled')) {
            return;
        }

        $cutoff = now()->subHours((int) config('store.pending_order_ttl_hours', 24));

        StoreOrder::where('status', StoreOrderStatus::PENDING)
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($stale) use ($orders) {
                foreach ($stale as $order) {
                    $orders->cancel($order, __('Cancelled automatically: payment was not completed in time.'));
                }
            });
    }
}

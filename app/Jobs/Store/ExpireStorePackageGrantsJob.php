<?php

namespace App\Jobs\Store;

use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Models\StorePackageGrant;
use App\Services\StoreCommandDispatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Takes back what a buyer's time has run out on.
 *
 * A grant with an expiry is the record of a timed purchase — a 30-day rank, a month of flight. When
 * it lapses this marks it EXPIRED and runs the package's EXPIRY command set, which is the half that
 * actually removes the perk in game.
 *
 * Runs every five minutes. A grant that lapsed four minutes ago is not urgent; one that never
 * lapses at all is a package the server is giving away.
 */
class ExpireStorePackageGrantsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('longtask');
    }

    public function handle(StoreCommandDispatchService $dispatcher): void
    {
        StorePackageGrant::query()
            ->where('status', StorePackageGrantStatus::ACTIVE)
            // A null expiry is a permanent grant, and never lapses.
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->with(['orderItem.order', 'orderItem.package.commands.servers'])
            // Chunked by id rather than offset, which is what makes it safe to change the status of
            // the rows being paged over.
            ->chunkById(100, function ($grants) use ($dispatcher) {
                foreach ($grants as $grant) {
                    $this->expire($grant, $dispatcher);
                }
            });
    }

    /**
     * Dispatch the expiry commands, then mark the grant.
     *
     * Deliberately in that order. Dispatching is idempotent — the unique index on
     * store_order_deliveries means a repeat is a no-op — so a sweep interrupted between the two
     * steps simply re-dispatches nothing and marks the grant next time round. Marking first would
     * mean a crash in between left the grant expired with its commands never sent, and nothing to
     * find it again.
     */
    private function expire(StorePackageGrant $grant, StoreCommandDispatchService $dispatcher): void
    {
        $item = $grant->orderItem;
        $order = $item?->order;

        if ($order && $item) {
            try {
                $dispatcher->dispatchForItem($order, $item, StorePackageCommandTrigger::EXPIRY);
            } catch (\Throwable $exception) {
                // Left ACTIVE on purpose: the next sweep tries again rather than silently letting
                // the buyer keep something they have stopped paying for.
                Log::error('Store grant expiry dispatch failed.', [
                    'grant_id' => $grant->id,
                    'order_id' => $order->id,
                    'exception' => $exception->getMessage(),
                ]);

                return;
            }
        }

        // Conditional on the status so two overlapping sweeps cannot both claim it.
        StorePackageGrant::whereKey($grant->id)
            ->where('status', StorePackageGrantStatus::ACTIVE)
            ->update([
                'status' => StorePackageGrantStatus::EXPIRED,
                'updated_at' => now(),
            ]);
    }
}

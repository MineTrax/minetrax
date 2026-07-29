<?php

namespace App\Listeners;

use App\Events\StoreOrderRefunded;
use App\Models\User;
use App\Notifications\StoreChargebackStaffNotification;
use App\Services\StoreBanService;
use App\Settings\StoreSettings;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * The two things a lost dispute needs beyond taking the perks back: an optional store ban, and
 * staff being told.
 *
 * Grants are revoked by StoreOrderService and the revocation commands by
 * DispatchStoreOrderRevocationOnRefund. This is only the abuse and reporting half.
 */
class HandleStoreChargeback implements ShouldQueue
{
    public function __construct(
        private StoreSettings $settings,
        private StoreBanService $bans,
    ) {}

    public function handle(StoreOrderRefunded $event): void
    {
        if (! $event->isChargeback) {
            return;
        }

        $order = $event->order->loadMissing('items', 'user');
        $banned = false;

        if ($this->settings->auto_ban_on_chargeback) {
            // Skipped when something already matches, so a buyer who disputes three orders does not
            // collect three identical bans.
            $alreadyBanned = $this->bans->isBanned(
                $order->user,
                $order->player_uuid,
                $order->ip_address,
                $order->email
            );

            if (! $alreadyBanned) {
                $this->bans->banForChargeback(
                    $order->user,
                    $order->player_uuid,
                    $order->ip_address,
                    $order->email,
                    __('Automatic: chargeback on order :number', [
                        'number' => strtoupper(substr($order->uuid, 0, 8)),
                    ])
                );

                $banned = true;
            }
        }

        // Scoped by permission rather than role, so an install with its own staff roles still
        // notifies the right people.
        User::permission('read store_orders')
            ->get()
            ->each
            ->notify(new StoreChargebackStaffNotification($order, $banned));
    }
}

<?php

namespace App\Listeners;

use App\Events\StoreOrderPaid;
use App\Jobs\Store\SendStorePurchaseDiscordWebhookJob;
use App\Settings\StoreSettings;

/**
 * Announces the sale in Discord, on PAID rather than COMPLETED.
 *
 * Money arriving is the event a community cares about; whether the rank has reached the player yet is
 * the store's problem, and a deferred delivery would otherwise hold the announcement until the buyer
 * next logged in.
 */
class AnnounceStorePurchaseOnDiscord
{
    public function __construct(private StoreSettings $settings) {}

    public function handle(StoreOrderPaid $event): void
    {
        // Checked here as well as in the job so an install with no webhook configured never queues
        // anything at all.
        if (! trim((string) $this->settings->discord_purchase_webhook_url)) {
            return;
        }

        SendStorePurchaseDiscordWebhookJob::dispatch($event->order);
    }
}

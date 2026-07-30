<?php

namespace App\Jobs\Store;

use App\Models\StoreOrder;
use App\Services\StoreCurrencyService;
use App\Settings\StoreSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Announces a sale in a Discord channel.
 *
 * Distinct from the per-user `discord` notification channel, which direct-messages one member: this
 * posts publicly through an incoming webhook, which is what makes a purchase feel like an event in a
 * community rather than a receipt in somebody's inbox.
 *
 * On the default queue rather than longtask — it is a single HTTP call, and an announcement that
 * lands twenty minutes after the purchase has missed its moment.
 */
class SendStorePurchaseDiscordWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Discord rate-limits and occasionally 500s, both of which a retry fixes. Beyond three, the URL
     * is wrong and retrying will never help.
     */
    public int $tries = 3;

    public int $backoff = 30;

    /**
     * Discord green, for an embed that always reports good news.
     */
    private const EMBED_COLOUR = 0x22C55E;

    public function __construct(private StoreOrder $order)
    {
        $this->onQueue('default');
    }

    public function handle(StoreSettings $settings, StoreCurrencyService $currencies): void
    {
        $url = trim((string) $settings->discord_purchase_webhook_url);

        if ($url === '') {
            return;
        }

        $order = $this->order->fresh(['items', 'user']);

        if (! $order) {
            // Orders are never hard-deleted, so this is only reachable in a torn-down test database.
            return;
        }

        $response = Http::asJson()
            ->timeout(10)
            ->post($url, [
                'embeds' => [[
                    'title' => __('New purchase'),
                    'description' => $this->describe($order, $settings),
                    'color' => self::EMBED_COLOUR,
                    'fields' => [
                        [
                            'name' => __('Items'),
                            'value' => $this->itemLines($order),
                            'inline' => false,
                        ],
                        [
                            'name' => __('Total'),
                            // The order's own currency, never converted: a buyer who paid ¥3000 is
                            // announced as having paid ¥3000.
                            'value' => $currencies->format((int) $order->total, $order->currency),
                            'inline' => true,
                        ],
                    ],
                    'timestamp' => ($order->paid_at ?? now())->toIso8601String(),
                ]],
            ]);

        if ($response->failed()) {
            // Logged and left to the retry rather than thrown, because a misconfigured webhook must
            // never look like a failed sale.
            Log::warning('Store purchase Discord announcement failed.', [
                'order_id' => $order->id,
                'status' => $response->status(),
            ]);

            $this->release($this->backoff);
        }
    }

    /**
     * Who bought, in public.
     *
     * `hide_buyer_identity` covers a guest's Minecraft username as well as an account name — it is
     * still an identity, and a channel post is as public as a homepage list.
     */
    private function describe(StoreOrder $order, StoreSettings $settings): string
    {
        $buyer = $settings->hide_buyer_identity
            ? __('Someone')
            : ($order->user?->username ?? $order->player_username ?? __('A guest'));

        return __(':buyer just supported the server!', ['buyer' => $buyer]);
    }

    private function itemLines(StoreOrder $order): string
    {
        $lines = $order->items
            ->map(fn ($item) => '• '.$item->quantity.' × '.$item->package_name)
            ->all();

        return $lines === [] ? __('A purchase') : implode("\n", $lines);
    }
}

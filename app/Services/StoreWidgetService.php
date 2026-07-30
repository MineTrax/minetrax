<?php

namespace App\Services;

use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Settings\StoreSettings;
use Illuminate\Support\Facades\Cache;

/**
 * The public-facing summaries: this month's goal, who bought what recently, and who has spent most.
 *
 * Everything here is read by anyone who can see the homepage, so three rules hold throughout.
 *
 * Aggregates are summed from `base_total` — the order total converted at the rate in force when it
 * was placed — because summing today's rates would make last month's figure move whenever a rate did.
 *
 * Those base-currency sums are then **converted** into whatever currency the visitor is shopping in,
 * never merely formatted with their symbol: a base amount wearing a rupee sign reads as ₹91 when it
 * is really $91. The one exception is a recent purchase, which is shown in the currency it was
 * actually paid in — converting a real transaction at today's rate would misstate what the buyer paid.
 *
 * And every name passes through {@see self::buyerName()}, so `hide_buyer_identity` cannot be
 * forgotten in one of the three widgets.
 */
class StoreWidgetService
{
    /**
     * Statuses that represent money the store has received. Matches the statistics dashboard: a
     * partially refunded order still counts, because part of it was kept.
     */
    private const EARNING_STATUSES = ['paid', 'completed', 'partially_refunded'];

    /**
     * Long enough that a homepage refresh does not re-aggregate, short enough that a buyer sees
     * their own purchase appear while they are still looking at the page.
     */
    private const CACHE_SECONDS = 60;

    /**
     * Turns a ratio into a percentage. Named rather than written inline because a bare 100 beside a
     * money variable is exactly what the arch test exists to catch — this one scales a dimensionless
     * ratio, never an amount.
     */
    private const RATIO_TO_PERCENT = 100;

    public function __construct(
        private StoreSettings $settings,
        private StoreCurrencyService $currencies,
    ) {}

    /**
     * Everything the homepage and the storefront need, or null for a widget that is switched off.
     *
     * One payload rather than three calls, so the two pages cannot end up showing different sets.
     *
     * @return array{goal: ?array<string, mixed>, recentPurchases: ?array<int, array<string, mixed>>, topDonor: ?array<string, mixed>}
     */
    public function payload(): array
    {
        return [
            'goal' => $this->goal(),
            'recentPurchases' => $this->recentPurchases(),
            'topDonor' => $this->topDonor(),
        ];
    }

    /**
     * Progress towards this calendar month's target.
     *
     * A calendar month rather than a rolling 30 days: a goal is something a community talks about
     * ("we're at 60% for March"), and a window that slides makes yesterday's number unrepeatable.
     *
     * @return array<string, mixed>|null
     */
    public function goal(): ?array
    {
        $target = (int) $this->settings->purchase_goal_amount;

        // A bar against a target of nothing would either divide by zero or sit at 100% forever.
        if (! $this->settings->show_purchase_goal || $target <= 0) {
            return null;
        }

        // Cached in the base currency, which is currency-agnostic: the conversion below happens per
        // request, so one cache entry serves every visitor whatever they are shopping in.
        $raised = Cache::remember('store:widget:goal:'.now()->format('Y-m'), self::CACHE_SECONDS, function () {
            return (int) StoreOrder::whereIn('status', self::EARNING_STATUSES)
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('base_total');
        });

        $display = $this->currencies->resolve();

        return [
            // Base minor units. The formatted pair below is what a visitor reads; these stay in the
            // base currency so the figures are comparable with the statistics dashboard.
            'raised' => $raised,
            'target' => $target,
            'currency' => $display->code,
            // Converted, not relabelled. format() without a currency would stamp the visitor's
            // symbol onto a base-currency amount — ₹91.41 for $91.41 of revenue.
            'raised_formatted' => $this->currencies->format($this->toDisplay($raised, $display), $display),
            'target_formatted' => $this->currencies->format($this->toDisplay($target, $display), $display),
            // From the base amounts, so the bar is exact and cannot drift with conversion rounding.
            // Capped, because a community that beats its goal should see a full bar rather than one
            // that has overflowed its container.
            'percent' => min(self::RATIO_TO_PERCENT, (int) floor($raised / $target * self::RATIO_TO_PERCENT)),
            'is_reached' => $raised >= $target,
            'month' => now()->format('F Y'),
        ];
    }

    /**
     * A base-currency amount in the currency the visitor is shopping in.
     *
     * Both halves of the goal go through this with the same rate, so the pair a visitor reads stays
     * internally consistent even when neither figure is a round number in their currency.
     */
    private function toDisplay(int $baseMinor, StoreCurrency $display): int
    {
        return $this->currencies->fromBase($baseMinor, $display);
    }

    /**
     * The latest purchases, as much as the public is allowed to know about them.
     *
     * Amounts are formatted in the order's **own** currency, not converted: a buyer who paid ¥3000
     * is shown as having paid ¥3000.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function recentPurchases(int $limit = 5): ?array
    {
        if (! $this->settings->show_recent_purchases) {
            return null;
        }

        return Cache::remember('store:widget:recent-purchases:'.$limit, self::CACHE_SECONDS, function () use ($limit) {
            return StoreOrder::whereIn('status', self::EARNING_STATUSES)
                ->with(['user:id,name,username,profile_photo_path,settings', 'items:id,store_order_id,package_name,quantity'])
                ->latest('paid_at')
                ->latest('id')
                ->limit($limit)
                ->get()
                ->map(fn (StoreOrder $order) => [
                    'id' => $order->id,
                    'buyer' => $this->buyerName($order),
                    // Only when the buyer is named: an avatar identifies somebody just as well as a
                    // username does.
                    'buyer_user' => $this->settings->hide_buyer_identity ? null : $order->user?->only([
                        'id', 'name', 'username', 'profile_photo_path', 'settings',
                    ]),
                    'items' => $order->items->map(fn ($item) => [
                        'package_name' => $item->package_name,
                        'quantity' => (int) $item->quantity,
                    ])->values(),
                    'total_formatted' => $this->currencies->format((int) $order->total, $order->currency),
                    'purchased_at' => $order->paid_at ?? $order->created_at,
                ])
                ->values()
                ->all();
        });
    }

    /**
     * Whoever has spent the most this month.
     *
     * Grouped by `player_uuid` rather than by account: a guest checkout has no account, and the same
     * player buying once signed in and once as a guest is still the same person to the community.
     *
     * @return array<string, mixed>|null
     */
    public function topDonor(): ?array
    {
        if (! $this->settings->show_top_donor) {
            return null;
        }

        $top = Cache::remember('store:widget:top-donor:'.now()->format('Y-m'), self::CACHE_SECONDS, function () {
            return StoreOrder::whereIn('status', self::EARNING_STATUSES)
                ->where('created_at', '>=', now()->startOfMonth())
                ->whereNotNull('player_uuid')
                ->selectRaw('player_uuid, MAX(player_username) as player_username, MAX(user_id) as user_id, SUM(base_total) as spent')
                ->groupBy('player_uuid')
                ->orderByDesc('spent')
                ->limit(1)
                ->first();
        });

        if (! $top) {
            return null;
        }

        $spent = (int) $top->spent;
        $display = $this->currencies->resolve();

        return [
            'name' => $this->settings->hide_buyer_identity ? __('Anonymous') : $top->player_username,
            // Base minor units, as the goal does; the formatted figure is what a visitor reads.
            'spent' => $spent,
            'currency' => $display->code,
            // Converted, not relabelled: this is a SUM of base_total across however many currencies
            // this player paid in, so it has no native currency of its own to show it in.
            'spent_formatted' => $this->currencies->format($this->toDisplay($spent, $display), $display),
            'month' => now()->format('F Y'),
        ];
    }

    /**
     * Who to credit a purchase to, in public.
     *
     * A guest has no account, so their Minecraft username is the only name there is — and it is
     * still an identity, so `hide_buyer_identity` covers it too.
     */
    private function buyerName(StoreOrder $order): string
    {
        if ($this->settings->hide_buyer_identity) {
            return __('Anonymous');
        }

        return $order->user?->username ?? $order->player_username ?? __('A guest');
    }
}

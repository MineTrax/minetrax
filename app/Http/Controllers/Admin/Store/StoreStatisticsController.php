<?php

namespace App\Http\Controllers\Admin\Store;

use App\Enums\StoreOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Services\StoreCurrencyService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Inertia\Inertia;
use Inertia\Response;

/**
 * What the store has actually taken.
 *
 * Every revenue figure sums `base_total`, the order total converted at the rate in force when it was
 * placed. Converting at report time with today's rate would silently rewrite history: last month's
 * revenue would move every time a rate did.
 */
class StoreStatisticsController extends Controller
{
    /**
     * Statuses that represent money the store has received. A partially refunded order still
     * counts — part of it was kept, and the refunded part is reported separately.
     */
    private const EARNING_STATUSES = ['paid', 'completed', 'partially_refunded'];

    /**
     * Turns a ratio into a percentage. Named rather than written inline because a bare 100 next to a
     * money variable is exactly the mistake the arch test exists to catch — this one scales a
     * dimensionless ratio, never an amount.
     */
    private const RATIO_TO_PERCENT = 100;

    public function __construct(private StoreCurrencyService $currencies) {}

    public function index(): Response
    {
        $this->authorize('viewStatistics', StoreOrder::class);

        $days = request()->integer('days', 30);
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;

        $since = now()->subDays($days - 1)->startOfDay();
        $base = $this->currencies->base();

        return Inertia::render('Admin/Store/StatisticsStore', [
            'days' => $days,
            'baseCurrency' => [
                'code' => $base->code,
                'symbol' => $base->symbol,
                'exponent' => (int) $base->exponent,
            ],
            'totals' => $this->totals($days, $since),
            'revenueOverTime' => $this->revenueOverTime($since),
            'statusBreakdown' => $this->statusBreakdown(),
            'topPackages' => $this->topPackages($since),
            'gatewayBreakdown' => $this->gatewayBreakdown($since),
            'currencyBreakdown' => $this->currencyBreakdown($since),
        ]);
    }

    /**
     * Headline figures, each carrying its own formatted string so Vue never divides money.
     *
     * @return array<string, mixed>
     */
    private function totals(int $days, Carbon $since): array
    {
        $earning = fn () => StoreOrder::whereIn('status', self::EARNING_STATUSES);

        $allTime = (int) $earning()->sum('base_total');
        $inPeriod = (int) $earning()->where('created_at', '>=', $since)->sum('base_total');

        // The window immediately before this one, so a change figure compares like with like.
        $previous = (int) $earning()
            ->whereBetween('created_at', [$since->copy()->subDays($days), $since])
            ->sum('base_total');

        $ordersInPeriod = $earning()->where('created_at', '>=', $since)->count();

        // Refunds are dated by when the money went back, not by when the order was placed: a refund
        // this week against last month's order belongs in this week's figure.
        $refundedInPeriod = (int) StoreOrder::whereIn('status', ['refunded', 'partially_refunded', 'chargeback'])
            ->where('refunded_at', '>=', $since)
            ->sum('base_total');

        return [
            'revenue_all_time' => $this->money($allTime),
            'revenue_period' => $this->money($inPeriod),
            'revenue_previous_period' => $this->money($previous),
            'revenue_change_percent' => $previous > 0
                ? round((($inPeriod - $previous) / $previous) * self::RATIO_TO_PERCENT, 1)
                : null,
            'orders_period' => $ordersInPeriod,
            'pending_orders' => StoreOrder::where('status', StoreOrderStatus::PENDING)->count(),
            'average_order_value' => $this->money($ordersInPeriod > 0 ? intdiv($inPeriod, $ordersInPeriod) : 0),
            'refunded_period' => $this->money($refundedInPeriod),
            'customers_period' => $earning()->where('created_at', '>=', $since)
                ->distinct()
                ->count('player_uuid'),
        ];
    }

    /**
     * Daily revenue, with empty days filled in so the line does not jump from Monday to Thursday.
     *
     * @return array<int, array<string, mixed>>
     */
    private function revenueOverTime(Carbon $since): array
    {
        $rows = StoreOrder::whereIn('status', self::EARNING_STATUSES)
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as day, SUM(base_total) as revenue, COUNT(*) as orders')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy(fn ($row) => (string) $row->day);

        $base = $this->currencies->base();

        return collect(CarbonPeriod::create($since, now()->endOfDay()))
            ->map(function ($date) use ($rows, $base) {
                $row = $rows->get($date->toDateString());
                $revenue = (int) ($row->revenue ?? 0);

                return [
                    'day' => $date->toDateString(),
                    // Plotted as a decimal because echarts has no idea what a minor unit is, with
                    // the formatted string alongside it for the tooltip.
                    'revenue' => (float) $this->currencies->toDecimal($revenue, $base),
                    'revenue_formatted' => $this->currencies->format($revenue, $base),
                    'orders' => (int) ($row->orders ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function statusBreakdown(): array
    {
        return StoreOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => (string) $row->status->value,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Best sellers by units, priced at what was charged rather than what the package costs today.
     *
     * The line total is in the order's currency, so it is turned into base currency by its share of
     * the order — a ratio, which is exponent-free. Dividing by the rate directly would be wrong
     * across currencies with different minor units: ¥3000 / 150 is 20, not the 2000 minor units
     * that $20 actually is.
     *
     * @return array<int, array<string, mixed>>
     */
    private function topPackages(Carbon $since): array
    {
        return StoreOrderItem::query()
            ->join('store_orders', 'store_orders.id', '=', 'store_order_items.store_order_id')
            ->whereIn('store_orders.status', self::EARNING_STATUSES)
            ->where('store_orders.created_at', '>=', $since)
            ->selectRaw('store_order_items.package_name')
            ->selectRaw('SUM(store_order_items.quantity) as units')
            ->selectRaw('SUM(store_order_items.total / NULLIF(store_orders.total, 0) * store_orders.base_total) as revenue')
            ->groupBy('store_order_items.package_name')
            ->orderByDesc('units')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'package_name' => $row->package_name,
                'units' => (int) $row->units,
                'revenue_formatted' => $this->currencies->format((int) round((float) $row->revenue), $this->currencies->base()),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function gatewayBreakdown(Carbon $since): array
    {
        return StoreOrder::whereIn('status', self::EARNING_STATUSES)
            ->where('created_at', '>=', $since)
            ->selectRaw('gateway, COUNT(*) as orders, SUM(base_total) as revenue')
            ->groupBy('gateway')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                // An order fully covered by a gift card never reaches a gateway, so null is a real
                // and meaningful value here.
                'label' => $row->gateway?->value ?? 'none',
                'orders' => (int) $row->orders,
                'revenue_formatted' => $this->currencies->format((int) $row->revenue, $this->currencies->base()),
            ])
            ->all();
    }

    /**
     * Both figures matter here: what buyers were charged in their own currency, and what that came
     * to in the base currency the rest of the page reports in.
     *
     * @return array<int, array<string, mixed>>
     */
    private function currencyBreakdown(Carbon $since): array
    {
        return StoreOrder::whereIn('status', self::EARNING_STATUSES)
            ->where('created_at', '>=', $since)
            ->selectRaw('currency, COUNT(*) as orders, SUM(total) as native_total, SUM(base_total) as revenue')
            ->groupBy('currency')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'currency' => $row->currency,
                'orders' => (int) $row->orders,
                'native_formatted' => $this->currencies->format((int) $row->native_total, $row->currency),
                'revenue_formatted' => $this->currencies->format((int) $row->revenue, $this->currencies->base()),
            ])
            ->all();
    }

    /**
     * @return array{minor: int, formatted: string}
     */
    private function money(int $amountMinor): array
    {
        return [
            'minor' => $amountMinor,
            'formatted' => $this->currencies->format($amountMinor, $this->currencies->base()),
        ];
    }
}

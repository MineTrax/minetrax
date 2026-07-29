<?php

namespace Tests\Feature\Store;

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Revenue reporting. The figures all come from base_total, snapshotted per order, so a rate change
 * cannot move last month's revenue.
 */
class StoreStatisticsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();
    }

    private function order(array $attributes = []): StoreOrder
    {
        return StoreOrder::factory()->completed()->create(array_merge([
            'total' => 1000,
            'amount_due' => 1000,
            'base_total' => 1000,
            'currency' => 'USD',
            'exchange_rate' => 1,
            'gateway' => StorePaymentGateway::STRIPE,
        ], $attributes));
    }

    public function test_guest_and_non_staff_are_denied()
    {
        $this->get(route('admin.store.statistics.index'))->assertStatus(302);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.store.statistics.index'))->assertStatus(302);
    }

    public function test_reading_orders_is_not_enough_to_see_revenue()
    {
        // Support staff who can look up an order have no business seeing what the server earns, so
        // the page has its own permission.
        $staff = User::factory()->create();
        $staff->assignRole('moderator');
        $staff->givePermissionTo('read store_orders');

        $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(403);
    }

    public function test_the_statistics_permission_grants_access()
    {
        $staff = User::factory()->create();
        $staff->assignRole('moderator');
        $staff->givePermissionTo('view store_statistics');

        $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(200);
    }

    public function test_the_page_is_unavailable_when_the_module_is_disabled()
    {
        config(['store.enabled' => false]);

        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(403);
    }

    public function test_revenue_counts_only_orders_that_took_money()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->order(['base_total' => 1000]);
        $this->order(['base_total' => 500, 'status' => StoreOrderStatus::PAID]);
        // Neither of these is money the store has.
        $this->order(['base_total' => 9999, 'status' => StoreOrderStatus::PENDING]);
        $this->order(['base_total' => 8888, 'status' => StoreOrderStatus::CANCELLED]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Store/StatisticsStore')
                ->where('totals.revenue_period.minor', 1500)
                ->where('totals.orders_period', 2)
            );
    }

    public function test_a_partially_refunded_order_still_counts_as_revenue()
    {
        // Part of it was kept. The refunded part is reported separately rather than deducted here.
        $this->actingAs(User::whereId(1)->first());
        $this->order([
            'base_total' => 2000,
            'status' => StoreOrderStatus::PARTIALLY_REFUNDED,
            'refunded_at' => now(),
        ]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->where('totals.revenue_period.minor', 2000)
                ->where('totals.refunded_period.minor', 2000)
            );
    }

    public function test_refunds_are_dated_by_when_the_money_went_back()
    {
        // An order placed months ago and refunded today belongs in today's refund figure.
        $this->actingAs(User::whereId(1)->first());
        $this->order([
            'base_total' => 3000,
            'status' => StoreOrderStatus::REFUNDED,
            'created_at' => now()->subMonths(6),
            'refunded_at' => now()->subDay(),
        ]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->where('totals.refunded_period.minor', 3000)
                ->where('totals.revenue_period.minor', 0)
            );
    }

    public function test_mixed_currencies_sum_through_base_total()
    {
        // A ¥3000 order and a $20 order are the same money. Only base_total can add them up.
        $this->actingAs(User::whereId(1)->first());
        StoreCurrency::factory()->zeroDecimal()->create();

        $this->order(['currency' => 'USD', 'total' => 2000, 'base_total' => 2000, 'exchange_rate' => 1]);
        $this->order(['currency' => 'JPY', 'total' => 3000, 'base_total' => 2000, 'exchange_rate' => 150]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(function ($page) {
                $props = $page->toArray()['props'];

                $this->assertSame(4000, $props['totals']['revenue_period']['minor']);
                $this->assertSame('$40.00', $props['totals']['revenue_period']['formatted']);

                $byCurrency = collect($props['currencyBreakdown'])->keyBy('currency');
                // Charged in yen, reported in dollars, both shown.
                $this->assertSame('¥3,000', $byCurrency['JPY']['native_formatted']);
                $this->assertSame('$20.00', $byCurrency['JPY']['revenue_formatted']);
            });
    }

    public function test_the_daily_series_fills_in_days_with_no_sales()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->order(['created_at' => now()->subDays(2)]);

        $this->get(route('admin.store.statistics.index', ['days' => 7]))
            ->assertInertia(fn ($page) => $page
                // Seven days requested, seven points returned, whether or not anything sold.
                ->has('revenueOverTime', 7)
            );
    }

    public function test_an_unsupported_range_falls_back_to_thirty_days()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->get(route('admin.store.statistics.index', ['days' => 4000]))
            ->assertInertia(fn ($page) => $page->where('days', 30));
    }

    public function test_best_sellers_report_units_and_what_was_charged()
    {
        $this->actingAs(User::whereId(1)->first());
        $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'price' => 500]);
        $order = $this->order(['total' => 1500, 'base_total' => 1500]);
        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 3,
            'unit_price_original' => 500,
            'unit_price' => 500,
            'total' => 1500,
        ]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->where('topPackages.0.package_name', 'VIP Rank')
                ->where('topPackages.0.units', 3)
                ->where('topPackages.0.revenue_formatted', '$15.00')
            );
    }

    public function test_a_zero_decimal_line_is_not_multiplied_by_a_hundred()
    {
        // The best-seller figure is derived from the line's share of the order, which is a ratio and
        // therefore exponent-free. Dividing ¥3000 by the rate of 150 would report 20 minor units.
        $this->actingAs(User::whereId(1)->first());
        StoreCurrency::factory()->zeroDecimal()->create();

        $package = StorePackage::factory()->create(['name' => 'Yen Rank']);
        $order = $this->order([
            'currency' => 'JPY',
            'total' => 3000,
            'base_total' => 2000,
            'exchange_rate' => 150,
        ]);
        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 1,
            'unit_price_original' => 3000,
            'unit_price' => 3000,
            'total' => 3000,
        ]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->where('topPackages.0.revenue_formatted', '$20.00')
            );
    }

    public function test_the_change_figure_compares_against_the_previous_window()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->order(['base_total' => 2000, 'created_at' => now()->subDays(2)]);
        $this->order(['base_total' => 1000, 'created_at' => now()->subDays(10)]);

        $this->get(route('admin.store.statistics.index', ['days' => 7]))
            ->assertInertia(fn ($page) => $page
                ->where('totals.revenue_period.minor', 2000)
                ->where('totals.revenue_previous_period.minor', 1000)
                ->where('totals.revenue_change_percent', 100)
            );
    }

    public function test_the_average_order_value_is_revenue_over_paid_orders()
    {
        $this->actingAs(User::whereId(1)->first());
        $this->order(['base_total' => 1000]);
        $this->order(['base_total' => 3000]);

        $this->get(route('admin.store.statistics.index'))
            ->assertInertia(fn ($page) => $page
                ->where('totals.average_order_value.minor', 2000)
            );
    }

    public function test_an_empty_store_reports_zeroes_rather_than_failing()
    {
        $this->actingAs(User::whereId(1)->first());

        $this->get(route('admin.store.statistics.index'))
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('totals.revenue_period.minor', 0)
                ->where('totals.average_order_value.minor', 0)
                ->where('totals.revenue_change_percent', null)
                ->where('topPackages', [])
            );
    }
}

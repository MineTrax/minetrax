<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePaymentGateway;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
});

function order(array $attributes = []): StoreOrder
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

test('guest and non staff are denied', function () {
    $this->get(route('admin.store.statistics.index'))->assertStatus(302);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.store.statistics.index'))->assertStatus(302);
});

test('reading orders is not enough to see revenue', function () {
    // Support staff who can look up an order have no business seeing what the server earns, so
    // the page has its own permission.
    $staff = User::factory()->create();
    $staff->assignRole('moderator');
    $staff->givePermissionTo('read store_orders');

    $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(403);
});

test('the statistics permission grants access', function () {
    $staff = User::factory()->create();
    $staff->assignRole('moderator');
    $staff->givePermissionTo('view store_statistics');

    $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(200);
});

test('the page is unavailable when the module is disabled', function () {
    config(['store.enabled' => false]);

    $staff = User::factory()->create();
    $staff->assignRole('admin');

    $this->actingAs($staff)->get(route('admin.store.statistics.index'))->assertStatus(403);
});

test('revenue counts only orders that took money', function () {
    $this->actingAs(User::whereId(1)->first());

    order(['base_total' => 1000]);
    order(['base_total' => 500, 'status' => StoreOrderStatus::PAID]);

    // Neither of these is money the store has.
    order(['base_total' => 9999, 'status' => StoreOrderStatus::PENDING]);
    order(['base_total' => 8888, 'status' => StoreOrderStatus::CANCELLED]);

    $this->get(route('admin.store.statistics.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Store/StatisticsStore')
            ->where('totals.revenue_period.minor', 1500)
            ->where('totals.orders_period', 2)
        );
});

test('a partially refunded order still counts as revenue', function () {
    // Part of it was kept. The refunded part is reported separately rather than deducted here.
    $this->actingAs(User::whereId(1)->first());
    order([
        'base_total' => 2000,
        'status' => StoreOrderStatus::PARTIALLY_REFUNDED,
        'refunded_at' => now(),
    ]);

    $this->get(route('admin.store.statistics.index'))
        ->assertInertia(fn ($page) => $page
            ->where('totals.revenue_period.minor', 2000)
            ->where('totals.refunded_period.minor', 2000)
        );
});

test('refunds are dated by when the money went back', function () {
    // An order placed months ago and refunded today belongs in today's refund figure.
    $this->actingAs(User::whereId(1)->first());
    order([
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
});

test('mixed currencies sum through base total', function () {
    // A ¥3000 order and a $20 order are the same money. Only base_total can add them up.
    $this->actingAs(User::whereId(1)->first());
    StoreCurrency::factory()->zeroDecimal()->create();

    order(['currency' => 'USD', 'total' => 2000, 'base_total' => 2000, 'exchange_rate' => 1]);
    order(['currency' => 'JPY', 'total' => 3000, 'base_total' => 2000, 'exchange_rate' => 150]);

    $this->get(route('admin.store.statistics.index'))
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['totals']['revenue_period']['minor'])->toBe(4000);
            expect($props['totals']['revenue_period']['formatted'])->toBe('$40.00');

            $byCurrency = collect($props['currencyBreakdown'])->keyBy('currency');
            // Charged in yen, reported in dollars, both shown.
            expect($byCurrency['JPY']['native_formatted'])->toBe('¥3,000');
            expect($byCurrency['JPY']['revenue_formatted'])->toBe('$20.00');
        });
});

test('the daily series fills in days with no sales', function () {
    $this->actingAs(User::whereId(1)->first());
    order(['created_at' => now()->subDays(2)]);

    $this->get(route('admin.store.statistics.index', ['days' => 7]))
        ->assertInertia(fn ($page) => $page
            // Seven days requested, seven points returned, whether or not anything sold.
            ->has('revenueOverTime', 7)
        );
});

test('an unsupported range falls back to thirty days', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->get(route('admin.store.statistics.index', ['days' => 4000]))
        ->assertInertia(fn ($page) => $page->where('days', 30));
});

test('best sellers report units and what was charged', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->create(['name' => 'VIP Rank', 'price' => 500]);
    $order = order(['total' => 1500, 'base_total' => 1500]);
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
});

test('a zero decimal line is not multiplied by a hundred', function () {
    // The best-seller figure is derived from the line's share of the order, which is a ratio and
    // therefore exponent-free. Dividing ¥3000 by the rate of 150 would report 20 minor units.
    $this->actingAs(User::whereId(1)->first());
    StoreCurrency::factory()->zeroDecimal()->create();

    $package = StorePackage::factory()->create(['name' => 'Yen Rank']);
    $order = order([
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
});

test('the change figure compares against the previous window', function () {
    $this->actingAs(User::whereId(1)->first());
    order(['base_total' => 2000, 'created_at' => now()->subDays(2)]);
    order(['base_total' => 1000, 'created_at' => now()->subDays(10)]);

    $this->get(route('admin.store.statistics.index', ['days' => 7]))
        ->assertInertia(fn ($page) => $page
            ->where('totals.revenue_period.minor', 2000)
            ->where('totals.revenue_previous_period.minor', 1000)
            ->where('totals.revenue_change_percent', 100)
        );
});

test('the average order value is revenue over paid orders', function () {
    $this->actingAs(User::whereId(1)->first());
    order(['base_total' => 1000]);
    order(['base_total' => 3000]);

    $this->get(route('admin.store.statistics.index'))
        ->assertInertia(fn ($page) => $page
            ->where('totals.average_order_value.minor', 2000)
        );
});

test('an empty store reports zeroes rather than failing', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->get(route('admin.store.statistics.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->where('totals.revenue_period.minor', 0)
            ->where('totals.average_order_value.minor', 0)
            ->where('totals.revenue_change_percent', null)
            ->where('topPackages', [])
        );
});

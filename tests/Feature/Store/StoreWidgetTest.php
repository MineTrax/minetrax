<?php

use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\User;
use App\Services\StoreCurrencyService;
use App\Services\StoreWidgetService;
use App\Settings\GeneralSettings;
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // The widgets cache their aggregates for a minute, which would leak between tests.
    Cache::flush();
});

/**
 * @param  array<string, mixed>  $values
 */
function widgetSettings(array $values): StoreSettings
{
    $settings = app(StoreSettings::class);

    foreach ($values as $key => $value) {
        $settings->{$key} = $value;
    }

    $settings->save();

    return $settings;
}

function widgets(): StoreWidgetService
{
    // Resolved fresh so it reads the settings the test just saved.
    return new StoreWidgetService(app(StoreSettings::class), app(StoreCurrencyService::class));
}

// -- Goal -------------------------------------------------------------------------------------

test('the goal is hidden when the toggle is off', function () {
    widgetSettings(['show_purchase_goal' => false, 'purchase_goal_amount' => 10000]);

    expect(widgets()->goal())->toBeNull();
});

test('the goal is hidden when no target is set', function () {
    // A bar against a target of nothing would divide by zero or sit at 100% forever.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 0]);

    expect(widgets()->goal())->toBeNull();
});

test('the goal sums this months earning orders', function () {
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);

    StoreOrder::factory()->completed()->create(['base_total' => 2500]);
    StoreOrder::factory()->paid()->create(['base_total' => 1500]);

    $goal = widgets()->goal();

    expect($goal['raised'])->toBe(4000);
    expect($goal['raised_formatted'])->toBe('$40.00');
    expect($goal['target_formatted'])->toBe('$100.00');
    expect($goal['percent'])->toBe(40);
    expect($goal['is_reached'])->toBeFalse();
});

test('the goal ignores orders that took no money', function () {
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);

    StoreOrder::factory()->completed()->create(['base_total' => 2500]);
    StoreOrder::factory()->create(['status' => 'pending', 'base_total' => 9999]);
    StoreOrder::factory()->create(['status' => 'cancelled', 'base_total' => 9999]);
    StoreOrder::factory()->create(['status' => 'refunded', 'base_total' => 9999]);

    expect(widgets()->goal()['raised'])->toBe(2500);
});

test('the goal ignores last months orders', function () {
    // A calendar month, so the figure a community quotes at each other stays quotable.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);

    StoreOrder::factory()->completed()->create(['base_total' => 2500]);
    StoreOrder::factory()->completed()->create([
        'base_total' => 9999,
        'created_at' => now()->startOfMonth()->subDay(),
    ]);

    expect(widgets()->goal()['raised'])->toBe(2500);
});

test('beating the goal fills the bar rather than overflowing it', function () {
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 1000]);

    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    $goal = widgets()->goal();

    expect($goal['percent'])->toBe(100);
    expect($goal['is_reached'])->toBeTrue();
});

// -- Recent purchases -------------------------------------------------------------------------

test('recent purchases are hidden when the toggle is off', function () {
    widgetSettings(['show_recent_purchases' => false]);

    expect(widgets()->recentPurchases())->toBeNull();
});

test('recent purchases list the newest paid orders first', function () {
    widgetSettings(['show_recent_purchases' => true, 'hide_buyer_identity' => false]);

    $older = StoreOrder::factory()->completed()->create(['paid_at' => now()->subDay()]);
    $older->items()->create(['package_name' => 'Bronze Rank', 'quantity' => 1, 'unit_price_original' => 100, 'unit_price' => 100, 'total' => 100]);

    $newer = StoreOrder::factory()->completed()->create(['paid_at' => now()]);
    $newer->items()->create(['package_name' => 'Gold Rank', 'quantity' => 1, 'unit_price_original' => 500, 'unit_price' => 500, 'total' => 500]);

    $purchases = widgets()->recentPurchases();

    expect($purchases)->toHaveCount(2);
    expect($purchases[0]['id'])->toBe($newer->id);
    expect($purchases[0]['items'][0]['package_name'])->toBe('Gold Rank');
});

test('a purchase amount is shown in the currency it was paid in', function () {
    // ¥3000 is 3000 minor units, not 300000: converting or rescaling it would misstate the sale.
    widgetSettings(['show_recent_purchases' => true]);
    StoreCurrency::factory()->zeroDecimal()->create();

    $order = StoreOrder::factory()->completed()->create(['currency' => 'JPY', 'total' => 3000]);
    $order->items()->create(['package_name' => 'Gold Rank', 'quantity' => 1, 'unit_price_original' => 3000, 'unit_price' => 3000, 'total' => 3000]);

    expect(widgets()->recentPurchases()[0]['total_formatted'])->toContain('3,000');
});

test('a members username is credited on their purchase', function () {
    widgetSettings(['show_recent_purchases' => true, 'hide_buyer_identity' => false]);

    $buyer = User::factory()->create(['username' => 'bigspender']);
    StoreOrder::factory()->completed()->create(['user_id' => $buyer->id]);

    $purchase = widgets()->recentPurchases()[0];

    expect($purchase['buyer'])->toBe('bigspender');
    expect($purchase['buyer_user'])->not->toBeNull();
});

test('a guest is credited by their minecraft username', function () {
    widgetSettings(['show_recent_purchases' => true, 'hide_buyer_identity' => false]);

    StoreOrder::factory()->completed()->create(['user_id' => null, 'player_username' => 'Notch']);

    expect(widgets()->recentPurchases()[0]['buyer'])->toBe('Notch');
});

test('hiding buyer identity anonymises the list', function () {
    widgetSettings(['show_recent_purchases' => true, 'hide_buyer_identity' => true]);

    $buyer = User::factory()->create(['username' => 'bigspender']);
    StoreOrder::factory()->completed()->create(['user_id' => $buyer->id, 'player_username' => 'Notch']);

    $purchase = widgets()->recentPurchases()[0];

    expect($purchase['buyer'])->toBe('Anonymous');
    expect($purchase['buyer'])->not->toBe('bigspender');
    // An avatar identifies somebody as surely as a username does, so it goes too.
    expect($purchase['buyer_user'])->toBeNull();
});

test('hiding buyer identity also anonymises a guests minecraft username', function () {
    // Not an account, but still an identity — and a searchable one.
    widgetSettings(['show_recent_purchases' => true, 'hide_buyer_identity' => true]);

    StoreOrder::factory()->completed()->create(['user_id' => null, 'player_username' => 'Notch']);

    expect(widgets()->recentPurchases()[0]['buyer'])->toBe('Anonymous');
});

// -- Top donor --------------------------------------------------------------------------------

test('the top donor is hidden when the toggle is off', function () {
    widgetSettings(['show_top_donor' => false]);

    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    expect(widgets()->topDonor())->toBeNull();
});

test('the top donor is whoever spent most this month', function () {
    widgetSettings(['show_top_donor' => true, 'hide_buyer_identity' => false]);

    StoreOrder::factory()->completed()->create([
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        'player_username' => 'Notch',
        'base_total' => 1000,
    ]);
    StoreOrder::factory()->completed()->create([
        'player_uuid' => 'c06f8906-4c8a-4911-9c29-ea1dbd1aab82',
        'player_username' => 'Jeb',
        'base_total' => 4000,
    ]);

    $donor = widgets()->topDonor();

    expect($donor['name'])->toBe('Jeb');
    expect($donor['spent'])->toBe(4000);
    expect($donor['spent_formatted'])->toBe('$40.00');
});

test('the top donors purchases are added up across their orders', function () {
    // Grouped by player, so somebody who bought three times outranks a single larger sale.
    widgetSettings(['show_top_donor' => true, 'hide_buyer_identity' => false]);

    foreach ([1500, 1500, 1500] as $amount) {
        StoreOrder::factory()->completed()->create([
            'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
            'player_username' => 'Notch',
            'base_total' => $amount,
        ]);
    }
    StoreOrder::factory()->completed()->create([
        'player_uuid' => 'c06f8906-4c8a-4911-9c29-ea1dbd1aab82',
        'player_username' => 'Jeb',
        'base_total' => 4000,
    ]);

    expect(widgets()->topDonor()['name'])->toBe('Notch');
    expect(widgets()->topDonor()['spent'])->toBe(4500);
});

test('hiding buyer identity anonymises the top donor', function () {
    widgetSettings(['show_top_donor' => true, 'hide_buyer_identity' => true]);

    StoreOrder::factory()->completed()->create(['player_username' => 'Notch', 'base_total' => 4000]);

    expect(widgets()->topDonor()['name'])->toBe('Anonymous');
});

test('there is no top donor before anybody has bought anything', function () {
    widgetSettings(['show_top_donor' => true]);

    expect(widgets()->topDonor())->toBeNull();
});

// -- Wiring -----------------------------------------------------------------------------------

test('the dashboard receives the widget payload', function () {
    widgetSettings([
        'show_purchase_goal' => true,
        'purchase_goal_amount' => 10000,
        'show_recent_purchases' => true,
        'show_top_donor' => true,
    ]);

    StoreOrder::factory()->completed()->create(['base_total' => 2500, 'player_username' => 'Notch']);

    $this->get(route('home.dashboard'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('storeWidgets.goal.percent', 25)
            ->has('storeWidgets.recentPurchases', 1)
            ->where('storeWidgets.topDonor.name', 'Notch')
        );
});

test('the storefront receives the widget payload', function () {
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);

    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    $this->get(route('store.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Store/IndexStore')
            ->where('storeWidgets.goal.percent', 50)
        );
});

test('the widgets are all off on the dashboard when the module is disabled', function () {
    // They are the one part of the store that renders outside the store's own pages, so the module
    // toggle has to reach them explicitly — there is no policy in the way.
    widgetSettings([
        'show_purchase_goal' => true,
        'purchase_goal_amount' => 10000,
        'show_recent_purchases' => true,
        'show_top_donor' => true,
    ]);
    StoreOrder::factory()->completed()->create(['base_total' => 2500]);

    config(['store.enabled' => false]);

    $this->get(route('home.dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('storeWidgets.goal', null)
            ->where('storeWidgets.recentPurchases', null)
            ->where('storeWidgets.topDonor', null)
        );
});

test('the widgets reach the homepage when the store owns it', function () {
    // With homepage_route = store, `/` is the storefront, so the boxes have to come with it.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);
    $general = app(GeneralSettings::class);
    $general->homepage_route = 'store';
    $general->save();

    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    $this->get('/')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Store/IndexStore')
            ->where('storeWidgets.goal.percent', 50)
        );
});

// -- Currency conversion ----------------------------------------------------------------------
//
// The reported bug: switching currency changed the symbol on the goal and the top supporter but not
// the number, so a base-currency amount was read as though it were rupees. Both are SUMs of
// `base_total`, which has no native currency of its own — they have to be converted, not relabelled.

test('the goal is converted into the currency the visitor is shopping in', function () {
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 80]);
    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    session(['store_currency' => 'INR']);
    $goal = widgets()->goal();

    // $50 of $100 at 80 INR to the dollar is ₹4,000 of ₹8,000 — not ₹50 of ₹100.
    expect($goal['raised_formatted'])->toBe('₹4,000.00');
    expect($goal['target_formatted'])->toBe('₹8,000.00');
    expect($goal['currency'])->toBe('INR');
});

test('the goal percentage is unaffected by the display currency', function () {
    // Computed from the base amounts, so conversion rounding cannot move the bar.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 83.47]);
    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    $inBase = widgets()->goal()['percent'];

    session(['store_currency' => 'INR']);
    Cache::flush();

    expect(widgets()->goal()['percent'])->toBe($inBase);
    expect($inBase)->toBe(50);
});

test('the goal keeps its base amounts alongside the converted strings', function () {
    // So the figure stays comparable with the statistics dashboard, which never converts.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 80]);
    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    session(['store_currency' => 'INR']);
    $goal = widgets()->goal();

    expect($goal['raised'])->toBe(5000);
    expect($goal['target'])->toBe(10000);
});

test('the top supporter figure is converted too', function () {
    widgetSettings(['show_top_donor' => true, 'hide_buyer_identity' => false]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 80]);
    StoreOrder::factory()->completed()->create(['player_username' => 'Notch', 'base_total' => 5000]);

    session(['store_currency' => 'INR']);
    $donor = widgets()->topDonor();

    expect($donor['spent_formatted'])->toBe('₹4,000.00');
    expect($donor['spent'])->toBe(5000);
    expect($donor['currency'])->toBe('INR');
});

test('a zero decimal display currency is not scaled by a hundred', function () {
    // ¥ has no minor unit, so $50 at 150 yen to the dollar is ¥7,500 — not ¥750,000.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000, 'show_top_donor' => true]);
    StoreCurrency::factory()->zeroDecimal()->create();
    StoreOrder::factory()->completed()->create(['player_username' => 'Notch', 'base_total' => 5000]);

    session(['store_currency' => 'JPY']);
    $goal = widgets()->goal();

    expect($goal['raised_formatted'])->toBe('¥7,500');
    expect($goal['target_formatted'])->toBe('¥15,000');
    expect(widgets()->topDonor()['spent_formatted'])->toBe('¥7,500');
});

test('one cache entry serves every currency', function () {
    // The aggregate is cached in the base currency and converted per request; caching the converted
    // figure would serve the first visitor's currency to everybody for the next minute.
    widgetSettings(['show_purchase_goal' => true, 'purchase_goal_amount' => 10000]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 80]);
    StoreOrder::factory()->completed()->create(['base_total' => 5000]);

    expect(widgets()->goal()['raised_formatted'])->toBe('$50.00');

    session(['store_currency' => 'INR']);

    // Deliberately no Cache::flush(): the cached value must be currency-agnostic.
    expect(widgets()->goal()['raised_formatted'])->toBe('₹4,000.00');
});

test('a recent purchase still shows what was actually paid', function () {
    // Deliberately not converted: the buyer paid $1.49 and a rate applied today would misstate it.
    widgetSettings(['show_recent_purchases' => true]);
    StoreCurrency::factory()->create(['code' => 'INR', 'symbol' => '₹', 'rate_to_base' => 80]);
    $order = StoreOrder::factory()->completed()->create(['currency' => 'USD', 'total' => 149]);
    $order->items()->create(['package_name' => 'Crate Key', 'quantity' => 1, 'unit_price_original' => 149, 'unit_price' => 149, 'total' => 149]);

    session(['store_currency' => 'INR']);

    expect(widgets()->recentPurchases()[0]['total_formatted'])->toBe('$1.49');
});

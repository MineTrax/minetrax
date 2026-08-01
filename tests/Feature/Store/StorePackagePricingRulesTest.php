<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\Player;
use App\Models\Server;
use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\User;
use App\Services\StoreCartService;
use App\Services\StoreOrderService;
use App\Services\StorePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    $this->enableStoreGateways(['manual']);

    $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

    $this->withoutMiddleware([
        ThrottleRequests::class,
        ThrottleRequestsWithRedis::class,
    ]);
});

/**
 * @param  array<int, array<string, mixed>>  $lines
 * @return array<string, mixed>
 */
function packagePricingRulesQuote(array $lines): array
{
    return app(StorePricingService::class)->quote($lines);
}

function packagePricingRulesCheckout(array $overrides = []): TestResponse
{
    return test()->post(route('store.checkout.store'), array_merge([
        'player_username' => 'Steve',
        'email' => 'buyer@example.com',
        'gateway' => 'manual',
        'accept_terms' => true,
    ], $overrides));
}

test('a package discount comes off the list price', function () {
    $package = StorePackage::factory()->discounted(2000)->create(['price' => 1000]);

    $quote = packagePricingRulesQuote([['package' => $package, 'quantity' => 2]]);

    expect($quote['items'][0]['unit_price_original'])->toBe(1000);
    expect($quote['items'][0]['unit_price'])->toBe(800);
    expect($quote['total'])->toBe(1600);

    // Every automatic reduction lands in the same aggregate the order column records.
    expect($quote['sale_discount'])->toBe(400);
});

test('a fractional discount percentage rounds down rather than giving money away', function () {
    // 12.5% of 999 is 124.875. Discounting 125 would be a rounding error in the buyer's
    // favour on every single sale.
    $package = StorePackage::factory()->discounted(1250)->create(['price' => 999]);

    expect(packagePricingRulesQuote([['package' => $package, 'quantity' => 1]])['items'][0]['unit_price'])->toBe(875);
});

test('a hundred percent discount makes the package free but never negative', function () {
    $package = StorePackage::factory()->discounted(10000)->create(['price' => 1000]);

    expect(packagePricingRulesQuote([['package' => $package, 'quantity' => 1]])['total'])->toBe(0);
});

test('the storefront shows both the discounted and the original price', function () {
    StorePackage::factory()->discounted(2500)->create(['price' => 2000, 'name' => 'Discounted rank']);

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $package = $page->toArray()['props']['packages'][0];

            expect($package['price'])->toBe(1500);
            expect($package['price_original'])->toBe(2000);
            expect($package['discount_bp'])->toBe(2500);
        });
});

test('a pay what you want package charges the amount the buyer chose', function () {
    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'custom_price' => '12.34',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('store_cart_items', [
        'store_package_id' => $package->id,
        'custom_price' => 1234,
        'custom_price_currency' => 'USD',
    ]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.total', 1234));
});

test('a pay what you want amount below the minimum is rejected', function () {
    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'custom_price' => '1.00',
    ])->assertSessionHasErrors(['custom_price']);

    $this->assertDatabaseCount('store_cart_items', 0);
});

test('a pay what you want amount above the maximum is rejected', function () {
    $package = StorePackage::factory()->payWhatYouWant(5000)->create(['price' => 500]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 1, 'custom_price' => '75.00',
    ])->assertSessionHasErrors(['custom_price']);
});

test('a pay what you want package falls back to its minimum when no amount is given', function () {
    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page->where('quote.total', 500));
});

test('a pay what you want line is always a quantity of one', function () {
    // Two chosen amounts have no sensible sum, so the line is fixed at one.
    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500, 'max_quantity' => 10]);

    $this->post(route('store.cart.store'), [
        'package_id' => $package->id, 'quantity' => 4, 'custom_price' => '20.00',
    ]);

    $this->assertDatabaseHas('store_cart_items', ['store_package_id' => $package->id, 'quantity' => 1]);
});

test('a chosen amount is converted when the cart is quoted in another currency', function () {
    StoreCurrency::factory()->create([
        'code' => 'EUR', 'exponent' => 2, 'symbol' => '€', 'is_base' => false, 'rate_to_base' => 2,
    ]);

    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 100]);

    // €20 chosen, then the buyer switches to the base currency: at 2 EUR per USD that is $10.
    $quote = packagePricingRulesQuote([[
        'package' => $package, 'quantity' => 1, 'custom_price' => 2000, 'custom_price_currency' => 'EUR',
    ]]);

    expect($quote['total'])->toBe(1000);
});

test('a chosen amount in a currency that is no longer enabled falls back to the minimum', function () {
    // There is no rate to value it with, and guessing would either overcharge or undercharge.
    $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 750]);

    $quote = packagePricingRulesQuote([[
        'package' => $package, 'quantity' => 1, 'custom_price' => 9999, 'custom_price_currency' => 'ZWL',
    ]]);

    expect($quote['total'])->toBe(750);
});

test('neither a discount nor a sale applies to a pay what you want package', function () {
    $package = StorePackage::factory()->payWhatYouWant()->discounted(5000)->create(['price' => 500]);

    $quote = packagePricingRulesQuote([[
        'package' => $package, 'quantity' => 1, 'custom_price' => 1000, 'custom_price_currency' => 'USD',
    ]]);

    expect($quote['items'][0]['unit_price'])->toBe(1000);
    expect($quote['sale_discount'])->toBe(0);
});

test('a per player limit only counts that players purchases', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'player_purchase_limit' => 1]);

    // Somebody else's paid order must not consume Steve's allowance.
    $other = StoreOrder::factory()->paid()->create(['player_uuid' => Player::factory()->create()->uuid]);
    $other->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    packagePricingRulesCheckout()->assertSessionHasNoErrors();

    StoreOrder::where('player_uuid', $player->uuid)->update(['status' => StoreOrderStatus::PAID]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
});

test('a limit for everyone counts every players purchases', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

    $other = StoreOrder::factory()->paid()->create(['player_uuid' => Player::factory()->create()->uuid]);
    $other->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
});

test('a cancelled order releases the allowance it held', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

    $cancelled = StoreOrder::factory()->create(['status' => StoreOrderStatus::CANCELLED]);
    $cancelled->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    packagePricingRulesCheckout()->assertSessionHasNoErrors();
});

test('a full refund puts the stock back on the shelf', function () {
    // The storefront reads sold_count for its out-of-stock badge while checkout counts
    // paid-state order items, so the two have to agree after a refund.
    $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

    $order = packagePricingRulesPayFor($package);
    expect((int) $package->fresh()->sold_count)->toBe(1);

    app(StoreOrderService::class)->refund($order->fresh(), (int) $order->amount_due);

    expect((int) $package->fresh()->sold_count)->toBe(0);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    packagePricingRulesCheckout()->assertSessionHasNoErrors();
});

// --- Required packages -------------------------------------------------------------------
function gateBehind(StorePackage $package, StorePackage $requirement, StorePackageRequirementMode $mode = StorePackageRequirementMode::ALL): void
{
    $package->requiredPackages()->attach($requirement->id);
    $package->update(['required_packages_mode' => $mode]);
}

test('a package with an unmet requirement cannot be bought', function () {
    Player::factory()->create(['username' => 'Steve']);
    $rank2 = StorePackage::factory()->create(['price' => 2000, 'name' => 'Rank 2']);
    $rank1 = StorePackage::factory()->create(['price' => 1000, 'name' => 'Rank 1']);
    gateBehind($rank2, $rank1);

    $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
    $this->assertDatabaseCount('store_orders', 0);
});

test('an active grant satisfies a requirement', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    $rank2 = StorePackage::factory()->create(['price' => 2000]);
    $rank1 = StorePackage::factory()->create(['price' => 1000]);
    gateBehind($rank2, $rank1);

    $earlier = StoreOrder::factory()->completed()->create(['player_uuid' => $player->uuid]);
    $item = $earlier->items()->create([
        'store_package_id' => $rank1->id, 'package_name' => $rank1->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);
    $item->grant()->create([
        'store_package_id' => $rank1->id,
        'player_uuid' => $player->uuid,
        'status' => StorePackageGrantStatus::ACTIVE,
        'granted_at' => now(),
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasNoErrors();
});

test('a revoked grant does not satisfy a requirement', function () {
    $player = Player::factory()->create(['username' => 'Steve']);
    $rank2 = StorePackage::factory()->create(['price' => 2000]);
    $rank1 = StorePackage::factory()->create(['price' => 1000]);
    gateBehind($rank2, $rank1);

    $earlier = StoreOrder::factory()->completed()->create(['player_uuid' => $player->uuid]);
    $item = $earlier->items()->create([
        'store_package_id' => $rank1->id, 'package_name' => $rank1->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);
    $item->grant()->create([
        'store_package_id' => $rank1->id,
        'player_uuid' => $player->uuid,
        'status' => StorePackageGrantStatus::REVOKED,
        'granted_at' => now(),
        'revoked_at' => now(),
    ]);

    $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
});

test('buying a requirement in the same order satisfies it', function () {
    Player::factory()->create(['username' => 'Steve']);
    $rank2 = StorePackage::factory()->create(['price' => 2000]);
    $rank1 = StorePackage::factory()->create(['price' => 1000]);
    gateBehind($rank2, $rank1);

    $this->post(route('store.cart.store'), ['package_id' => $rank1->id, 'quantity' => 1]);
    $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasNoErrors();
    expect((int) StoreOrder::first()->total)->toBe(3000);
});

test('require all needs every listed package', function () {
    Player::factory()->create(['username' => 'Steve']);
    $target = StorePackage::factory()->create(['price' => 3000]);
    $first = StorePackage::factory()->create(['price' => 1000]);
    $second = StorePackage::factory()->create(['price' => 1000]);
    $target->requiredPackages()->attach([$first->id, $second->id]);
    $target->update(['required_packages_mode' => StorePackageRequirementMode::ALL]);

    $this->post(route('store.cart.store'), ['package_id' => $first->id, 'quantity' => 1]);
    $this->post(route('store.cart.store'), ['package_id' => $target->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
});

test('require one is satisfied by any single package', function () {
    Player::factory()->create(['username' => 'Steve']);
    $target = StorePackage::factory()->create(['price' => 3000]);
    $first = StorePackage::factory()->create(['price' => 1000]);
    $second = StorePackage::factory()->create(['price' => 1000]);
    $target->requiredPackages()->attach([$first->id, $second->id]);
    $target->update(['required_packages_mode' => StorePackageRequirementMode::ANY]);

    $this->post(route('store.cart.store'), ['package_id' => $first->id, 'quantity' => 1]);
    $this->post(route('store.cart.store'), ['package_id' => $target->id, 'quantity' => 1]);

    packagePricingRulesCheckout()->assertSessionHasNoErrors();
});

test('a member cannot send a non giftable package to another player', function () {
    $user = User::factory()->create();
    $own = Player::factory()->create(['username' => 'Mine']);
    $user->players()->attach($own->id);
    Player::factory()->create(['username' => 'Someone']);

    $package = StorePackage::factory()->create(['price' => 1000, 'is_giftable' => false]);

    $this->actingAs($user);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    packagePricingRulesCheckout(['player_username' => 'Someone'])
        ->assertSessionHasErrors(['player_username']);
});

test('a member can send a giftable package to another player', function () {
    $user = User::factory()->create();
    $own = Player::factory()->create(['username' => 'Mine']);
    $user->players()->attach($own->id);
    $friend = Player::factory()->create(['username' => 'Someone']);

    $package = StorePackage::factory()->giftable()->create(['price' => 1000]);

    $this->actingAs($user);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    packagePricingRulesCheckout(['player_username' => 'Someone'])->assertSessionHasNoErrors();

    expect(StoreOrder::first()->player_uuid)->toBe($friend->uuid);
});

test('a member can always buy a non giftable package for their own player', function () {
    $user = User::factory()->create();
    $own = Player::factory()->create(['username' => 'Mine']);
    $user->players()->attach($own->id);

    $package = StorePackage::factory()->create(['price' => 1000, 'is_giftable' => false]);

    $this->actingAs($user);
    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

    packagePricingRulesCheckout(['player_username' => 'Mine'])->assertSessionHasNoErrors();
});

test('a featured package is listed first regardless of sort order', function () {
    StorePackage::factory()->create(['name' => 'Ordinary', 'sort_order' => 0]);
    StorePackage::factory()->featured()->create(['name' => 'Highlighted', 'sort_order' => 99]);

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $packages = $page->toArray()['props']['packages'];

            expect($packages[0]['name'])->toBe('Highlighted');
            expect($packages[0]['is_featured'])->toBeTrue();
        });
});

test('a package scheduled for later is neither listed nor reachable', function () {
    $package = StorePackage::factory()->window(now()->addDay()->toDateTimeString())->create();

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('packages', 0));

    $this->get(route('store.package', $package->slug))->assertNotFound();
});

test('a package past its removal date is neither listed nor reachable', function () {
    $package = StorePackage::factory()->window(null, now()->subMinute()->toDateTimeString())->create();

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('packages', 0));

    $this->get(route('store.package', $package->slug))->assertNotFound();
});

test('a package inside its window is listed normally', function () {
    StorePackage::factory()
        ->window(now()->subDay()->toDateTimeString(), now()->addDay()->toDateTimeString())
        ->create();

    $this->get(route('store.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('packages', 1));
});

test('a package cannot be added to the cart outside its window', function () {
    $package = StorePackage::factory()->window(null, now()->subMinute()->toDateTimeString())->create();

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
        ->assertNotFound();
});

test('a package that closes while it sits in the cart blocks checkout', function () {
    Player::factory()->create(['username' => 'Steve']);
    $package = StorePackage::factory()->create(['price' => 1000]);

    $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
    $package->update(['available_until' => now()->subMinute()]);

    packagePricingRulesCheckout()->assertSessionHasErrors(['cart']);
});

// --- Package type and gift card issuance --------------------------------------------------
function packagePricingRulesPayFor(StorePackage $package, int $quantity = 1): StoreOrder
{
    if (! Player::where('username', 'Steve')->exists()) {
        Player::factory()->create(['username' => 'Steve']);
    }

    test()->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => $quantity]);
    packagePricingRulesCheckout()->assertSessionHasNoErrors();

    $order = StoreOrder::latest('id')->first();
    app(StoreOrderService::class)->markPaid(
        $order,
        $order->payments()->first(),
        (int) $order->amount_due,
        $order->currency
    );

    return $order->fresh();
}

test('a gift card package issues a code worth the configured amount', function () {
    $package = StorePackage::factory()->giftCard(5000)->create(['price' => 5000]);

    $order = packagePricingRulesPayFor($package);
    $card = $order->items->first()->giftCard;

    expect($card)->not->toBeNull('A gift card package must mint a code.');
    expect((int) $card->balance)->toBe(5000);
    expect($card->currency_code)->toBe('USD');
    $this->assertDatabaseHas('store_gift_card_transactions', [
        'store_gift_card_id' => $card->id,
        'type' => 'issue',
        'amount' => 5000,
    ]);
});

test('same as package price issues a card worth what the buyer actually paid', function () {
    // Discounted to 800, so the credit is 800 rather than the 1000 on the price tag.
    $package = StorePackage::factory()->giftCard(null, true)->discounted(2000)->create(['price' => 1000]);

    $order = packagePricingRulesPayFor($package);

    expect((int) $order->items->first()->giftCard->balance)->toBe(800);
});

test('a gift card amount multiplies by the quantity bought', function () {
    $package = StorePackage::factory()->giftCard(1000)->create(['price' => 1000, 'max_quantity' => 5]);

    $order = packagePricingRulesPayFor($package, 3);

    expect((int) $order->items->first()->giftCard->balance)->toBe(3000);
});

test('re running fulfilment does not mint a second code', function () {
    $package = StorePackage::factory()->giftCard(2500)->create(['price' => 2500]);

    $order = packagePricingRulesPayFor($package);
    $first = $order->items->first()->giftCard->id;

    ProcessStoreOrderPurchaseJob::dispatch($order);

    expect(StoreGiftCard::count())->toBe(1);
    expect($order->fresh()->items->first()->giftCard->id)->toBe($first);
});

test('an issued code can be redeemed against a later order', function () {
    $package = StorePackage::factory()->giftCard(5000)->create(['price' => 5000]);
    $code = packagePricingRulesPayFor($package)->items->first()->giftCard->code;

    $rank = StorePackage::factory()->create(['price' => 2000]);
    $this->post(route('store.cart.store'), ['package_id' => $rank->id, 'quantity' => 1]);
    $this->post(route('store.cart.code'), ['code' => $code]);

    $this->get(route('store.cart.show'))
        ->assertInertia(fn ($page) => $page
            ->where('quote.gift_card_amount', 2000)
            ->where('quote.amount_due', 0)
        );
});

test('a gift card only package runs no commands even if some are left on it', function () {
    $package = StorePackage::factory()->giftCard(1000)->create(['price' => 1000]);
    StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
    Server::factory()->create();

    packagePricingRulesPayFor($package);

    $this->assertDatabaseCount('store_order_deliveries', 0);
    $this->assertDatabaseCount('command_queues', 0);
});

test('a package and giftcard both delivers in game and issues credit', function () {
    $package = StorePackage::factory()->packageAndGiftCard(1000)->create(['price' => 4000]);
    StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
    Server::factory()->create();

    $order = packagePricingRulesPayFor($package);

    expect($order->items->first()->giftCard)->not->toBeNull();
    $this->assertDatabaseCount('store_order_deliveries', 1);
});

test('switching a package away from selling credit clears its amount', function () {
    $this->actingAs(User::whereId(1)->first());
    $package = StorePackage::factory()->giftCard(5000)->create(['price' => 5000]);

    $this->put(route('admin.store.package.update', $package->id), [
        'name' => $package->name,
        'type' => StorePackageType::MINECRAFT_PACKAGE->value,
        'price' => 5000,
        'is_pay_what_you_want' => false,
        'is_gift_card_amount_same_as_price' => false,
        'gift_card_amount' => 5000,
        'is_visible' => true,
        'is_enabled' => true,
        'requires_login' => false,
        'is_featured' => false,
        'is_giftable' => false,
        'min_quantity' => 1,
        'required_packages_mode' => StorePackageRequirementMode::ALL->value,
    ])->assertSessionHasNoErrors();

    expect($package->fresh()->gift_card_amount)->toBeNull();
});

test('a gift card package must carry an amount', function () {
    $this->actingAs(User::whereId(1)->first());

    $this->post(route('admin.store.package.store'), [
        'name' => 'Store Credit',
        'type' => StorePackageType::GIFTCARD->value,
        'price' => 5000,
        'is_pay_what_you_want' => false,
        'is_gift_card_amount_same_as_price' => false,
        'gift_card_amount' => null,
        'is_visible' => true,
        'is_enabled' => true,
        'requires_login' => false,
        'is_featured' => false,
        'is_giftable' => false,
        'min_quantity' => 1,
        'required_packages_mode' => StorePackageRequirementMode::ALL->value,
    ])->assertSessionHasErrors(['gift_card_amount']);
});

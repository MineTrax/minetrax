<?php

namespace Tests\Feature\Store;

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
use App\Settings\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The package configuration rules that decide what a customer may buy and what they pay:
 * pay-what-you-want, a per-package discount, the two purchase limits, prerequisites, gifting,
 * featured ordering, the package type, and the publish window.
 */
class StorePackagePricingRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['store.enabled' => true]);
        $this->baseCurrency();

        $settings = app(StoreSettings::class);
        $settings->enabled_gateways = ['manual'];
        $settings->save();

        $this->withCookie(StoreCartService::COOKIE, 'guest-cart-token');

        $this->withoutMiddleware([
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<string, mixed>
     */
    private function quote(array $lines): array
    {
        return app(StorePricingService::class)->quote($lines);
    }

    private function checkout(array $overrides = []): TestResponse
    {
        return $this->post(route('store.checkout.store'), array_merge([
            'player_username' => 'Steve',
            'email' => 'buyer@example.com',
            'gateway' => 'manual',
            'accept_terms' => true,
        ], $overrides));
    }

    // --- Discount percentage -----------------------------------------------------------------

    public function test_a_package_discount_comes_off_the_list_price()
    {
        $package = StorePackage::factory()->discounted(2000)->create(['price' => 1000]);

        $quote = $this->quote([['package' => $package, 'quantity' => 2]]);

        $this->assertSame(1000, $quote['items'][0]['unit_price_original']);
        $this->assertSame(800, $quote['items'][0]['unit_price']);
        $this->assertSame(1600, $quote['total']);
        // Every automatic reduction lands in the same aggregate the order column records.
        $this->assertSame(400, $quote['sale_discount']);
    }

    public function test_a_fractional_discount_percentage_rounds_down_rather_than_giving_money_away()
    {
        // 12.5% of 999 is 124.875. Discounting 125 would be a rounding error in the buyer's
        // favour on every single sale.
        $package = StorePackage::factory()->discounted(1250)->create(['price' => 999]);

        $this->assertSame(875, $this->quote([['package' => $package, 'quantity' => 1]])['items'][0]['unit_price']);
    }

    public function test_a_hundred_percent_discount_makes_the_package_free_but_never_negative()
    {
        $package = StorePackage::factory()->discounted(10000)->create(['price' => 1000]);

        $this->assertSame(0, $this->quote([['package' => $package, 'quantity' => 1]])['total']);
    }

    public function test_the_storefront_shows_both_the_discounted_and_the_original_price()
    {
        StorePackage::factory()->discounted(2500)->create(['price' => 2000, 'name' => 'Discounted rank']);

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(function ($page) {
                $package = $page->toArray()['props']['packages'][0];

                $this->assertSame(1500, $package['price']);
                $this->assertSame(2000, $package['price_original']);
                $this->assertSame(2500, $package['discount_bp']);
            });
    }

    // --- Pay what you want -------------------------------------------------------------------

    public function test_a_pay_what_you_want_package_charges_the_amount_the_buyer_chose()
    {
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
    }

    public function test_a_pay_what_you_want_amount_below_the_minimum_is_rejected()
    {
        $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'custom_price' => '1.00',
        ])->assertSessionHasErrors(['custom_price']);

        $this->assertDatabaseCount('store_cart_items', 0);
    }

    public function test_a_pay_what_you_want_amount_above_the_maximum_is_rejected()
    {
        $package = StorePackage::factory()->payWhatYouWant(5000)->create(['price' => 500]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 1, 'custom_price' => '75.00',
        ])->assertSessionHasErrors(['custom_price']);
    }

    public function test_a_pay_what_you_want_package_falls_back_to_its_minimum_when_no_amount_is_given()
    {
        $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page->where('quote.total', 500));
    }

    public function test_a_pay_what_you_want_line_is_always_a_quantity_of_one()
    {
        // Two chosen amounts have no sensible sum, so the line is fixed at one.
        $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 500, 'max_quantity' => 10]);

        $this->post(route('store.cart.store'), [
            'package_id' => $package->id, 'quantity' => 4, 'custom_price' => '20.00',
        ]);

        $this->assertDatabaseHas('store_cart_items', ['store_package_id' => $package->id, 'quantity' => 1]);
    }

    public function test_a_chosen_amount_is_converted_when_the_cart_is_quoted_in_another_currency()
    {
        StoreCurrency::factory()->create([
            'code' => 'EUR', 'exponent' => 2, 'symbol' => '€', 'is_base' => false, 'rate_to_base' => 2,
        ]);

        $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 100]);

        // €20 chosen, then the buyer switches to the base currency: at 2 EUR per USD that is $10.
        $quote = $this->quote([[
            'package' => $package, 'quantity' => 1, 'custom_price' => 2000, 'custom_price_currency' => 'EUR',
        ]]);

        $this->assertSame(1000, $quote['total']);
    }

    public function test_a_chosen_amount_in_a_currency_that_is_no_longer_enabled_falls_back_to_the_minimum()
    {
        // There is no rate to value it with, and guessing would either overcharge or undercharge.
        $package = StorePackage::factory()->payWhatYouWant()->create(['price' => 750]);

        $quote = $this->quote([[
            'package' => $package, 'quantity' => 1, 'custom_price' => 9999, 'custom_price_currency' => 'ZWL',
        ]]);

        $this->assertSame(750, $quote['total']);
    }

    public function test_neither_a_discount_nor_a_sale_applies_to_a_pay_what_you_want_package()
    {
        $package = StorePackage::factory()->payWhatYouWant()->discounted(5000)->create(['price' => 500]);

        $quote = $this->quote([[
            'package' => $package, 'quantity' => 1, 'custom_price' => 1000, 'custom_price_currency' => 'USD',
        ]]);

        $this->assertSame(1000, $quote['items'][0]['unit_price']);
        $this->assertSame(0, $quote['sale_discount']);
    }

    // --- Purchase limits ---------------------------------------------------------------------

    public function test_a_per_player_limit_only_counts_that_players_purchases()
    {
        $player = Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000, 'player_purchase_limit' => 1]);

        // Somebody else's paid order must not consume Steve's allowance.
        $other = StoreOrder::factory()->paid()->create(['player_uuid' => Player::factory()->create()->uuid]);
        $other->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasNoErrors();

        StoreOrder::where('player_uuid', $player->uuid)->update(['status' => StoreOrderStatus::PAID]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasErrors(['cart']);
    }

    public function test_a_limit_for_everyone_counts_every_players_purchases()
    {
        Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

        $other = StoreOrder::factory()->paid()->create(['player_uuid' => Player::factory()->create()->uuid]);
        $other->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasErrors(['cart']);
    }

    public function test_a_cancelled_order_releases_the_allowance_it_held()
    {
        Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

        $cancelled = StoreOrder::factory()->create(['status' => StoreOrderStatus::CANCELLED]);
        $cancelled->items()->create([
            'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
            'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
        ]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasNoErrors();
    }

    public function test_a_full_refund_puts_the_stock_back_on_the_shelf()
    {
        // The storefront reads sold_count for its out-of-stock badge while checkout counts
        // paid-state order items, so the two have to agree after a refund.
        $package = StorePackage::factory()->create(['price' => 1000, 'global_purchase_limit' => 1]);

        $order = $this->payFor($package);
        $this->assertSame(1, (int) $package->fresh()->sold_count);

        app(StoreOrderService::class)->refund($order->fresh(), (int) $order->amount_due);

        $this->assertSame(0, (int) $package->fresh()->sold_count);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $this->checkout()->assertSessionHasNoErrors();
    }

    // --- Required packages -------------------------------------------------------------------

    private function gateBehind(StorePackage $package, StorePackage $requirement, StorePackageRequirementMode $mode = StorePackageRequirementMode::ALL): void
    {
        $package->requiredPackages()->attach($requirement->id);
        $package->update(['required_packages_mode' => $mode]);
    }

    public function test_a_package_with_an_unmet_requirement_cannot_be_bought()
    {
        Player::factory()->create(['username' => 'Steve']);
        $rank2 = StorePackage::factory()->create(['price' => 2000, 'name' => 'Rank 2']);
        $rank1 = StorePackage::factory()->create(['price' => 1000, 'name' => 'Rank 1']);
        $this->gateBehind($rank2, $rank1);

        $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

        $this->checkout()->assertSessionHasErrors(['cart']);
        $this->assertDatabaseCount('store_orders', 0);
    }

    public function test_an_active_grant_satisfies_a_requirement()
    {
        $player = Player::factory()->create(['username' => 'Steve']);
        $rank2 = StorePackage::factory()->create(['price' => 2000]);
        $rank1 = StorePackage::factory()->create(['price' => 1000]);
        $this->gateBehind($rank2, $rank1);

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

        $this->checkout()->assertSessionHasNoErrors();
    }

    public function test_a_revoked_grant_does_not_satisfy_a_requirement()
    {
        $player = Player::factory()->create(['username' => 'Steve']);
        $rank2 = StorePackage::factory()->create(['price' => 2000]);
        $rank1 = StorePackage::factory()->create(['price' => 1000]);
        $this->gateBehind($rank2, $rank1);

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

        $this->checkout()->assertSessionHasErrors(['cart']);
    }

    public function test_buying_a_requirement_in_the_same_order_satisfies_it()
    {
        Player::factory()->create(['username' => 'Steve']);
        $rank2 = StorePackage::factory()->create(['price' => 2000]);
        $rank1 = StorePackage::factory()->create(['price' => 1000]);
        $this->gateBehind($rank2, $rank1);

        $this->post(route('store.cart.store'), ['package_id' => $rank1->id, 'quantity' => 1]);
        $this->post(route('store.cart.store'), ['package_id' => $rank2->id, 'quantity' => 1]);

        $this->checkout()->assertSessionHasNoErrors();
        $this->assertSame(3000, (int) StoreOrder::first()->total);
    }

    public function test_require_all_needs_every_listed_package()
    {
        Player::factory()->create(['username' => 'Steve']);
        $target = StorePackage::factory()->create(['price' => 3000]);
        $first = StorePackage::factory()->create(['price' => 1000]);
        $second = StorePackage::factory()->create(['price' => 1000]);
        $target->requiredPackages()->attach([$first->id, $second->id]);
        $target->update(['required_packages_mode' => StorePackageRequirementMode::ALL]);

        $this->post(route('store.cart.store'), ['package_id' => $first->id, 'quantity' => 1]);
        $this->post(route('store.cart.store'), ['package_id' => $target->id, 'quantity' => 1]);

        $this->checkout()->assertSessionHasErrors(['cart']);
    }

    public function test_require_one_is_satisfied_by_any_single_package()
    {
        Player::factory()->create(['username' => 'Steve']);
        $target = StorePackage::factory()->create(['price' => 3000]);
        $first = StorePackage::factory()->create(['price' => 1000]);
        $second = StorePackage::factory()->create(['price' => 1000]);
        $target->requiredPackages()->attach([$first->id, $second->id]);
        $target->update(['required_packages_mode' => StorePackageRequirementMode::ANY]);

        $this->post(route('store.cart.store'), ['package_id' => $first->id, 'quantity' => 1]);
        $this->post(route('store.cart.store'), ['package_id' => $target->id, 'quantity' => 1]);

        $this->checkout()->assertSessionHasNoErrors();
    }

    // --- Gifting ------------------------------------------------------------------------------

    public function test_a_member_cannot_send_a_non_giftable_package_to_another_player()
    {
        $user = User::factory()->create();
        $own = Player::factory()->create(['username' => 'Mine']);
        $user->players()->attach($own->id);
        Player::factory()->create(['username' => 'Someone']);

        $package = StorePackage::factory()->create(['price' => 1000, 'is_giftable' => false]);

        $this->actingAs($user);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->checkout(['player_username' => 'Someone'])
            ->assertSessionHasErrors(['player_username']);
    }

    public function test_a_member_can_send_a_giftable_package_to_another_player()
    {
        $user = User::factory()->create();
        $own = Player::factory()->create(['username' => 'Mine']);
        $user->players()->attach($own->id);
        $friend = Player::factory()->create(['username' => 'Someone']);

        $package = StorePackage::factory()->giftable()->create(['price' => 1000]);

        $this->actingAs($user);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->checkout(['player_username' => 'Someone'])->assertSessionHasNoErrors();

        $this->assertSame($friend->uuid, StoreOrder::first()->player_uuid);
    }

    public function test_a_member_can_always_buy_a_non_giftable_package_for_their_own_player()
    {
        $user = User::factory()->create();
        $own = Player::factory()->create(['username' => 'Mine']);
        $user->players()->attach($own->id);

        $package = StorePackage::factory()->create(['price' => 1000, 'is_giftable' => false]);

        $this->actingAs($user);
        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);

        $this->checkout(['player_username' => 'Mine'])->assertSessionHasNoErrors();
    }

    // --- Featured -----------------------------------------------------------------------------

    public function test_a_featured_package_is_listed_first_regardless_of_sort_order()
    {
        StorePackage::factory()->create(['name' => 'Ordinary', 'sort_order' => 0]);
        StorePackage::factory()->featured()->create(['name' => 'Highlighted', 'sort_order' => 99]);

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(function ($page) {
                $packages = $page->toArray()['props']['packages'];

                $this->assertSame('Highlighted', $packages[0]['name']);
                $this->assertTrue($packages[0]['is_featured']);
            });
    }

    // --- Publish window -----------------------------------------------------------------------

    public function test_a_package_scheduled_for_later_is_neither_listed_nor_reachable()
    {
        $package = StorePackage::factory()->window(now()->addDay()->toDateTimeString())->create();

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('packages', 0));

        $this->get(route('store.package', $package->slug))->assertNotFound();
    }

    public function test_a_package_past_its_removal_date_is_neither_listed_nor_reachable()
    {
        $package = StorePackage::factory()->window(null, now()->subMinute()->toDateTimeString())->create();

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('packages', 0));

        $this->get(route('store.package', $package->slug))->assertNotFound();
    }

    public function test_a_package_inside_its_window_is_listed_normally()
    {
        StorePackage::factory()
            ->window(now()->subDay()->toDateTimeString(), now()->addDay()->toDateTimeString())
            ->create();

        $this->get(route('store.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('packages', 1));
    }

    public function test_a_package_cannot_be_added_to_the_cart_outside_its_window()
    {
        $package = StorePackage::factory()->window(null, now()->subMinute()->toDateTimeString())->create();

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1])
            ->assertNotFound();
    }

    public function test_a_package_that_closes_while_it_sits_in_the_cart_blocks_checkout()
    {
        Player::factory()->create(['username' => 'Steve']);
        $package = StorePackage::factory()->create(['price' => 1000]);

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => 1]);
        $package->update(['available_until' => now()->subMinute()]);

        $this->checkout()->assertSessionHasErrors(['cart']);
    }

    // --- Package type and gift card issuance --------------------------------------------------

    private function payFor(StorePackage $package, int $quantity = 1): StoreOrder
    {
        if (! Player::where('username', 'Steve')->exists()) {
            Player::factory()->create(['username' => 'Steve']);
        }

        $this->post(route('store.cart.store'), ['package_id' => $package->id, 'quantity' => $quantity]);
        $this->checkout()->assertSessionHasNoErrors();

        $order = StoreOrder::latest('id')->first();
        app(StoreOrderService::class)->markPaid(
            $order,
            $order->payments()->first(),
            (int) $order->amount_due,
            $order->currency
        );

        return $order->fresh();
    }

    public function test_a_gift_card_package_issues_a_code_worth_the_configured_amount()
    {
        $package = StorePackage::factory()->giftCard(5000)->create(['price' => 5000]);

        $order = $this->payFor($package);
        $card = $order->items->first()->giftCard;

        $this->assertNotNull($card, 'A gift card package must mint a code.');
        $this->assertSame(5000, (int) $card->balance);
        $this->assertSame('USD', $card->currency_code);
        $this->assertDatabaseHas('store_gift_card_transactions', [
            'store_gift_card_id' => $card->id,
            'type' => 'issue',
            'amount' => 5000,
        ]);
    }

    public function test_same_as_package_price_issues_a_card_worth_what_the_buyer_actually_paid()
    {
        // Discounted to 800, so the credit is 800 rather than the 1000 on the price tag.
        $package = StorePackage::factory()->giftCard(null, true)->discounted(2000)->create(['price' => 1000]);

        $order = $this->payFor($package);

        $this->assertSame(800, (int) $order->items->first()->giftCard->balance);
    }

    public function test_a_gift_card_amount_multiplies_by_the_quantity_bought()
    {
        $package = StorePackage::factory()->giftCard(1000)->create(['price' => 1000, 'max_quantity' => 5]);

        $order = $this->payFor($package, 3);

        $this->assertSame(3000, (int) $order->items->first()->giftCard->balance);
    }

    public function test_re_running_fulfilment_does_not_mint_a_second_code()
    {
        $package = StorePackage::factory()->giftCard(2500)->create(['price' => 2500]);

        $order = $this->payFor($package);
        $first = $order->items->first()->giftCard->id;

        ProcessStoreOrderPurchaseJob::dispatch($order);

        $this->assertSame(1, StoreGiftCard::count());
        $this->assertSame($first, $order->fresh()->items->first()->giftCard->id);
    }

    public function test_an_issued_code_can_be_redeemed_against_a_later_order()
    {
        $package = StorePackage::factory()->giftCard(5000)->create(['price' => 5000]);
        $code = $this->payFor($package)->items->first()->giftCard->code;

        $rank = StorePackage::factory()->create(['price' => 2000]);
        $this->post(route('store.cart.store'), ['package_id' => $rank->id, 'quantity' => 1]);
        $this->post(route('store.cart.code'), ['code' => $code]);

        $this->get(route('store.cart.show'))
            ->assertInertia(fn ($page) => $page
                ->where('quote.gift_card_amount', 2000)
                ->where('quote.amount_due', 0)
            );
    }

    public function test_a_gift_card_only_package_runs_no_commands_even_if_some_are_left_on_it()
    {
        $package = StorePackage::factory()->giftCard(1000)->create(['price' => 1000]);
        StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
        Server::factory()->create();

        $this->payFor($package);

        $this->assertDatabaseCount('store_order_deliveries', 0);
        $this->assertDatabaseCount('command_queues', 0);
    }

    public function test_a_package_and_giftcard_both_delivers_in_game_and_issues_credit()
    {
        $package = StorePackage::factory()->packageAndGiftCard(1000)->create(['price' => 4000]);
        StorePackageCommand::factory()->create(['store_package_id' => $package->id]);
        Server::factory()->create();

        $order = $this->payFor($package);

        $this->assertNotNull($order->items->first()->giftCard);
        $this->assertDatabaseCount('store_order_deliveries', 1);
    }

    public function test_switching_a_package_away_from_selling_credit_clears_its_amount()
    {
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

        $this->assertNull($package->fresh()->gift_card_amount);
    }

    public function test_a_gift_card_package_must_carry_an_amount()
    {
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
    }
}

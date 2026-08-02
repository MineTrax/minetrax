<?php

use App\Enums\StoreCommandTrigger;
use App\Enums\StoreOrderStatus;
use App\Enums\StoreReferralAttributionMode;
use App\Models\Server;
use App\Models\StoreBan;
use App\Models\StoreCategory;
use App\Models\StoreCommand;
use App\Models\StoreCoupon;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use App\Models\StoreSale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('every store table exists', function () {
    $tables = [
        'store_categories', 'store_packages', 'store_command_server', 'store_commands',
        'store_command_package',
        'store_currencies', 'store_package_prices',
        'store_carts', 'store_cart_items',
        'store_coupons', 'store_couponables', 'store_sales', 'store_saleables', 'store_gift_cards',
        'store_orders', 'store_order_items', 'store_package_grants', 'store_order_deliveries',
        'store_gift_card_transactions',
        'store_payments', 'store_payment_refunds', 'store_gateway_webhooks',
        'store_bans',
        'store_referrals', 'store_referral_payouts',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Missing table [{$table}].");
    }
});

test('no store migration uses a database enum column', function () {
    // Enumerated columns are plain strings cast to PHP backed enums. MySQL enum columns
    // require an ALTER to add a value, which makes shipping a new order status painful.
    foreach (glob(database_path('migrations/*create_store_*')) as $migration) {
        $this->assertStringNotContainsString(
            '->enum(',
            file_get_contents($migration),
            'Store migrations must not use $table->enum(): '.basename($migration)
        );
    }
});

test('catalog factories create valid records', function () {
    $category = StoreCategory::factory()->create();
    $package = StorePackage::factory()->create(['store_category_id' => $category->id]);

    $this->assertDatabaseHas('store_categories', ['id' => $category->id]);
    $this->assertDatabaseHas('store_packages', ['id' => $package->id]);
    expect($package->category->id)->toEqual($category->id);
    expect($category->packages->contains($package))->toBeTrue();
});

test('package states apply', function () {
    expect(StorePackage::factory()->expiring()->create()->expiry_duration_days)->toEqual(30);
    expect(StorePackage::factory()->disabled()->create()->is_enabled)->toBeFalse();
    expect(StorePackage::factory()->hidden()->create()->is_visible)->toBeFalse();
});

test('package commands can be filtered by trigger', function () {
    $package = StorePackage::factory()->create();
    StoreCommand::factory()->count(2)->forOwner($package)->create();
    StoreCommand::factory()->expiry()->forOwner($package)->create();

    expect($package->commands)->toHaveCount(3);
    expect($package->commandsForTrigger(StoreCommandTrigger::PURCHASE)->get())->toHaveCount(2);
    expect($package->commandsForTrigger(StoreCommandTrigger::EXPIRY)->get())->toHaveCount(1);
});

test('a command can target specific servers', function () {
    // Servers hang off the command, not the package, so two commands on one package can go
    // to different places.
    $command = StoreCommand::factory()->create(['is_run_on_all_servers' => false]);
    $server = Server::factory()->create();

    $command->servers()->attach($server);

    expect($command->fresh()->servers->contains($server))->toBeTrue();
    expect($command->fresh()->is_run_on_all_servers)->toBeFalse();
});

test('order factory creates a valid order with an auto generated uuid', function () {
    $order = StoreOrder::factory()->create();

    expect(Str::isUuid($order->uuid))->toBeTrue();
    expect($order->getRouteKeyName())->toEqual('uuid');
    expect($order->status)->toEqual(StoreOrderStatus::PENDING);
});

test('order relations resolve', function () {
    $user = User::factory()->create();
    $order = StoreOrder::factory()->forUser($user)->create();
    $item = StoreOrderItem::factory()->create(['store_order_id' => $order->id]);
    $payment = StorePayment::factory()->create(['store_order_id' => $order->id]);

    $order->refresh();

    expect($order->user->id)->toEqual($user->id);
    expect($order->items->contains($item))->toBeTrue();
    expect($order->payments->contains($payment))->toBeTrue();
    expect($item->order->id)->toEqual($order->id);
});

test('order factory states apply', function () {
    expect(StoreOrder::factory()->paid()->create()->status)->toEqual(StoreOrderStatus::PAID);

    $completed = StoreOrder::factory()->completed()->create();
    expect($completed->status)->toEqual(StoreOrderStatus::COMPLETED);
    expect($completed->completed_at)->not->toBeNull();

    expect(StoreOrder::factory()->guest()->create()->user_id)->toBeNull();
});

test('order totals are stored as integer minor units', function () {
    $order = StoreOrder::factory()->create(['total' => 1999, 'amount_due' => 1999]);

    expect($order->fresh()->total)->toBeInt();
    expect($order->fresh()->total)->toBe(1999);
});

test('order status enum serialises as key value for the frontend', function () {
    // BaseModel::attributesToArray turns HasKeyValueSerialization enums into {key, value},
    // which is what every Vue page in this codebase reads.
    $order = StoreOrder::factory()->create();
    $array = $order->toArray();

    expect($array['status'])->toBeArray();
    expect($array['status']['key'])->toEqual('PENDING');
    expect($array['status']['value'])->toEqual('pending');
});

test('order state machine permits only legal transitions', function () {
    expect(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::PAID))->toBeTrue();
    expect(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::CANCELLED))->toBeTrue();
    expect(StoreOrderStatus::PAID->canTransitionTo(StoreOrderStatus::COMPLETED))->toBeTrue();
    expect(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::REFUNDED))->toBeTrue();
    expect(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::CHARGEBACK))->toBeTrue();
    expect(StoreOrderStatus::PARTIALLY_REFUNDED->canTransitionTo(StoreOrderStatus::REFUNDED))->toBeTrue();

    // An out-of-order webhook must never be able to skip or reverse the lifecycle.
    expect(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::COMPLETED))->toBeFalse();
    expect(StoreOrderStatus::PENDING->canTransitionTo(StoreOrderStatus::REFUNDED))->toBeFalse();
    expect(StoreOrderStatus::COMPLETED->canTransitionTo(StoreOrderStatus::PENDING))->toBeFalse();
    expect(StoreOrderStatus::REFUNDED->canTransitionTo(StoreOrderStatus::COMPLETED))->toBeFalse();
    expect(StoreOrderStatus::CANCELLED->canTransitionTo(StoreOrderStatus::PAID))->toBeFalse();
});

test('terminal and paid states are classified correctly', function () {
    expect(StoreOrderStatus::REFUNDED->isTerminal())->toBeTrue();
    expect(StoreOrderStatus::CANCELLED->isTerminal())->toBeTrue();
    expect(StoreOrderStatus::CHARGEBACK->isTerminal())->toBeTrue();
    expect(StoreOrderStatus::PENDING->isTerminal())->toBeFalse();

    // Purchase limits count paid states only, so a cancelled order restocks automatically.
    expect(StoreOrderStatus::PAID->isPaidState())->toBeTrue();
    expect(StoreOrderStatus::COMPLETED->isPaidState())->toBeTrue();
    expect(StoreOrderStatus::PENDING->isPaidState())->toBeFalse();
    expect(StoreOrderStatus::CANCELLED->isPaidState())->toBeFalse();

    expect(StoreOrderStatus::REFUNDED->isRevoking())->toBeTrue();
    expect(StoreOrderStatus::CHARGEBACK->isRevoking())->toBeTrue();
    expect(StoreOrderStatus::PARTIALLY_REFUNDED->isRevoking())->toBeFalse();
});

test('currency factory covers non two decimal currencies', function () {
    $base = $this->baseCurrency();
    $yen = StoreCurrency::factory()->zeroDecimal()->create();
    $dinar = StoreCurrency::factory()->threeDecimal()->create();

    expect($base->is_base)->toBeTrue();
    expect($base->exponent)->toEqual(2);
    expect($yen->exponent)->toEqual(0, 'JPY has no minor unit.');
    expect($dinar->exponent)->toEqual(3, 'KWD has three minor-unit digits.');

    expect(StoreCurrency::base()->id)->toEqual($base->id);
    expect(StoreCurrency::enabled()->get())->toHaveCount(3);
});

test('delivery table has no status column', function () {
    // Delivery health is read through the joined command_queues row so there is exactly one
    // source of truth. A status column here would inevitably drift out of sync.
    expect(Schema::hasColumn('store_order_deliveries', 'status'))->toBeFalse();
    expect(Schema::hasColumn('store_order_deliveries', 'command_queue_id'))->toBeTrue();
});

test('cart items cache no package price', function () {
    // Carts are always priced live; a copied package price is a tampering and staleness
    // vector. custom_price is the one exception and is not a copy of anything: it is what the
    // buyer typed for a pay-what-you-want package, so it has nowhere else to live.
    expect(Schema::hasColumn('store_cart_items', 'price'))->toBeFalse();
    expect(Schema::hasColumn('store_cart_items', 'unit_price'))->toBeFalse();
    expect(Schema::hasColumn('store_cart_items', 'custom_price'))->toBeTrue();
    expect(Schema::hasColumn('store_cart_items', 'custom_price_currency'))->toBeTrue();
});

test('the delivery guard still keys on a single non null command id', function () {
    // The executable form of a load-bearing design decision. A sale's commands share
    // store_commands with a package's precisely so this column is never null: MySQL treats
    // a NULL inside a unique index as distinct, so a second nullable command column here would
    // switch the double-delivery guard off for every row that left it empty.
    expect(Schema::hasColumn('store_order_deliveries', 'store_command_id'))->toBeTrue();
    expect(Schema::hasColumn('store_order_deliveries', 'store_sale_command_id'))->toBeFalse();
    expect(Schema::hasColumn('store_order_deliveries', 'command_source'))->toBeFalse();

    $definition = DB::selectOne('SHOW CREATE TABLE store_order_deliveries')->{'Create Table'};

    expect($definition)->toContain('store_order_deliveries_unique_dispatch');
    expect($definition)->toContain('`store_order_item_id`,`store_command_id`,`server_id`,`trigger`,`repeat_index`');
});

test('a store command owner is a morph, not a column per kind', function () {
    // The owner columns are gone. Their absence is asserted rather than assumed, because a leftover
    // store_sale_id would still be readable and writable, and code half-migrated onto the morph
    // would keep working right up until the two disagreed.
    expect(Schema::hasColumn('store_commands', 'commandable_type'))->toBeTrue();
    expect(Schema::hasColumn('store_commands', 'commandable_id'))->toBeTrue();
    expect(Schema::hasColumn('store_commands', 'store_package_id'))->toBeFalse();
    expect(Schema::hasColumn('store_commands', 'store_sale_id'))->toBeFalse();

    expect(Schema::hasColumn('store_commands', 'is_run_on_all_packages'))->toBeTrue();

    // Unrelated to the command's owner: this is the sale an order line was priced under.
    expect(Schema::hasColumn('store_order_items', 'store_sale_id'))->toBeTrue();

    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->create();

    expect(StoreCommand::factory()->forOwner($package)->create()->commandable->is($package))->toBeTrue();
    expect(StoreCommand::factory()->forSale($sale)->create()->commandable->is($sale))->toBeTrue();
});

test('referral factory and relations resolve', function () {
    $user = User::factory()->create();
    $coupon = StoreCoupon::factory()->create();

    $referral = StoreReferral::factory()->forUser($user)->withCoupon($coupon)->create(['code' => 'KAKAMORA']);

    expect($referral->user->is($user))->toBeTrue();
    expect($referral->coupon->is($coupon))->toBeTrue();
    expect($referral->share_bp)->toBe(500);
    expect($referral->attribution_mode)->toEqual(StoreReferralAttributionMode::FIRST_TOUCH);

    // A referral owns commands like any other registered owner.
    $command = StoreCommand::factory()->forOwner($referral)->create();
    expect($command->fresh()->commandable->is($referral))->toBeTrue();

    // Soft deletes, because orders and payouts point here and both are part of a money trail.
    $referral->delete();
    expect(StoreReferral::withTrashed()->find($referral->id)->trashed())->toBeTrue();
});

test('the earning statuses track the state machine rather than a hardcoded list', function () {
    // If a new paid-ish status is ever added, the balance has to notice on its own. Listing them
    // here instead would leave money uncounted with nothing to fail.
    expect(StoreReferral::earningStatuses())
        ->toEqual(['paid', 'completed', 'partially_refunded']);

    foreach (StoreOrderStatus::cases() as $status) {
        expect(in_array($status->value, StoreReferral::earningStatuses(), true))
            ->toBe($status->isPaidState(), "[{$status->value}] disagrees with isPaidState().");
    }
});

test('what a referral is owed is earnings minus payouts', function () {
    $referral = StoreReferral::factory()->create();

    StoreOrder::factory()->paid()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 300,
    ]);
    StoreOrder::factory()->completed()->create([
        'store_referral_id' => $referral->id,
        'referral_earning_base' => 200,
    ]);
    // Cancelled and refunded orders owe nothing: one was never paid, the other was paid back.
    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::CANCELLED,
        'referral_earning_base' => 999,
    ]);
    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::REFUNDED,
        'referral_earning_base' => 999,
    ]);

    StoreReferralPayout::factory()->of(200)->create(['store_referral_id' => $referral->id]);

    expect($referral->earnedBase())->toBe(500);
    expect($referral->paidOut())->toBe(200);
    expect($referral->owed())->toBe(300);

    // The scope has to reach the same three numbers, or the listing and the detail page disagree.
    $scoped = StoreReferral::withBalance()->find($referral->id);

    expect($scoped->earnedBase())->toBe(500);
    expect($scoped->paidOut())->toBe(200);
    expect($scoped->owed())->toBe(300);
});

test('a refund landing after a payout leaves a negative balance rather than a clamped zero', function () {
    // Clamping would quietly forgive an overpayment, which is exactly the thing the owner needs to
    // see. It is carried against future earnings instead.
    $referral = StoreReferral::factory()->create();

    StoreOrder::factory()->create([
        'store_referral_id' => $referral->id,
        'status' => StoreOrderStatus::REFUNDED,
        'referral_earning_base' => 500,
    ]);

    StoreReferralPayout::factory()->of(500)->create(['store_referral_id' => $referral->id]);

    expect($referral->owed())->toBe(-500);
    expect(StoreReferral::withBalance()->find($referral->id)->owed())->toBe(-500);
});

test('ban factory and active scope', function () {
    StoreBan::factory()->create();
    StoreBan::factory()->create(['expires_at' => now()->subDay()]);
    StoreBan::factory()->create(['expires_at' => now()->addDay()]);

    expect(StoreBan::all())->toHaveCount(3);
    expect(StoreBan::active()->get())->toHaveCount(2, 'An expired ban must not block checkout.');
});

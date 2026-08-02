<?php

use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StorePackageType;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreCommand;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StoreReferral;
use App\Models\StoreSale;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreGiftCardService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // The job opens a real TCP socket to the server's webquery port otherwise, and waits out a
    // ten second connect timeout for every command.
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * A paid order carrying the given referral, with $lines packages on it.
 *
 * @param  array<int, StorePackage>  $packages
 */
function referralCommandOrder(?StoreReferral $referral, array $packages): StoreOrder
{
    $order = StoreOrder::factory()->paid()->create([
        'player_username' => 'Steve',
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
        'store_referral_id' => $referral?->id,
        'referral_code' => $referral?->code,
        'referral_share_bp' => $referral?->share_bp,
    ]);

    foreach ($packages as $package) {
        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => 1,
            'unit_price_original' => 1000,
            'unit_price' => 1000,
            'total' => 1000,
        ]);
    }

    return $order->fresh();
}

function referralDispatch(StoreOrder $order): void
{
    app(StoreCommandDispatchService::class)->dispatchForOrder($order, StoreCommandTrigger::PURCHASE);
}

test('a referred order runs the referral commands once, whatever is in the basket', function () {
    // The code was used on the order, not on any one line in it. Running per line would hand out
    // the same thank-you three times for a three-item basket.
    Server::factory()->create();
    $referral = StoreReferral::factory()->withCommands()->create(['code' => 'KAKAMORA', 'referrer_name' => 'Kakamora']);
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks {REFERRER_NAME}']);

    $order = referralCommandOrder($referral, [
        StorePackage::factory()->create(),
        StorePackage::factory()->create(),
        StorePackage::factory()->create(),
    ]);

    referralDispatch($order);

    expect(CommandQueue::where('tag', 'store')->count())->toBe(1);
    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toBe('say thanks Kakamora');
});

test('the delivery is anchored to the lowest id item, so a resend cannot double it', function () {
    Server::factory()->create();
    $referral = StoreReferral::factory()->withCommands()->create();
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks']);

    $order = referralCommandOrder($referral, [StorePackage::factory()->create(), StorePackage::factory()->create()]);

    referralDispatch($order);

    $delivery = StoreOrderDelivery::first();
    expect($delivery->store_order_item_id)->toBe($order->items->sortBy('id')->first()->id);

    // The unique index on store_order_deliveries is the whole reason for the anchor.
    referralDispatch($order->fresh());

    expect(StoreOrderDelivery::count())->toBe(1);
    expect(CommandQueue::where('tag', 'store')->count())->toBe(1);
});

test('both placeholders substitute', function () {
    Server::factory()->create();
    $referral = StoreReferral::factory()->withCommands()->create(['code' => 'KAKAMORA', 'referrer_name' => 'Kakamora']);
    StoreCommand::factory()->forOwner($referral)->create([
        'command' => 'broadcast {PLAYER_USERNAME} bought via {REFERRAL_CODE} from {REFERRER_NAME}',
    ]);

    referralDispatch(referralCommandOrder($referral, [StorePackage::factory()->create()]));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))
        ->toBe('broadcast Steve bought via KAKAMORA from Kakamora');
});

test('an unreferred order leaves no braces behind on a package command', function () {
    // str_ireplace() is deprecated for null in PHP 8.1+, and the literal braces reaching a live
    // server console is worse than an empty gap.
    Server::factory()->create();
    $package = StorePackage::factory()->create();
    StoreCommand::factory()->forOwner($package)->create([
        'command' => 'broadcast {PLAYER_USERNAME} {REFERRAL_CODE} {REFERRER_NAME}',
    ]);

    referralDispatch(referralCommandOrder(null, [$package]));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toBe('broadcast Steve  ');
});

test('the toggle is what decides, not merely having commands', function () {
    Server::factory()->create();
    $referral = StoreReferral::factory()->create(); // is_command_execution_enabled defaults to false
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks']);

    referralDispatch(referralCommandOrder($referral, [StorePackage::factory()->create()]));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(0);
});

test('a gift card only order still runs the referral commands', function () {
    // dispatchForItem() bails on a package that delivers nothing in game, which is why the referral
    // is dispatched from dispatchForOrder() rather than folded into the per-item path.
    Server::factory()->create();
    $referral = StoreReferral::factory()->withCommands()->create();
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks']);
    $package = StorePackage::factory()->create(['type' => StorePackageType::GIFTCARD, 'gift_card_amount' => 500]);

    referralDispatch(referralCommandOrder($referral, [$package]));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(1);
});

test('package, sale and referral commands all land on one order', function () {
    // The three owners share store_commands and one delivery table, so this is the test that says
    // the morph actually works end to end.
    Server::factory()->create();
    $package = StorePackage::factory()->create();
    StoreCommand::factory()->forOwner($package)->create(['command' => 'lp user {PLAYER_USERNAME} parent add vip']);

    $sale = StoreSale::factory()->create();
    StoreCommand::factory()->forSale($sale)->create(['command' => 'give {PLAYER_USERNAME} coins 100']);

    $referral = StoreReferral::factory()->withCommands()->create(['referrer_name' => 'Kakamora']);
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks {REFERRER_NAME}']);

    $order = referralCommandOrder($referral, []);
    $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 1000,
        'unit_price' => 900,
        'total' => 900,
        'store_sale_id' => $sale->id,
        'sale_name' => $sale->name,
    ]);

    referralDispatch($order->fresh());

    expect(CommandQueue::where('tag', 'store')->pluck('parsed_command')->all())->toEqual([
        'lp user Steve parent add vip',
        'give Steve coins 100',
        'say thanks Kakamora',
    ]);
});

test('a referral command with no reachable server is counted as skipped rather than lost', function () {
    // No server has a webquery port, so nothing could receive this. The order must not report a
    // clean delivery.
    Server::factory()->create(['webquery_port' => null]);
    $referral = StoreReferral::factory()->withCommands()->create();
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks']);

    $order = referralCommandOrder($referral, [StorePackage::factory()->create()]);
    $status = app(StoreCommandDispatchService::class)->dispatchForOrder($order, StoreCommandTrigger::PURCHASE);

    expect(CommandQueue::where('tag', 'store')->count())->toBe(0);
    expect($status)->toBe(StoreDeliveryStatus::FAILED);
});

test('the purchase job runs the referral commands too', function () {
    // dispatchForOrder is reached through the job in production, so the wiring is asserted rather
    // than assumed.
    Server::factory()->create();
    $referral = StoreReferral::factory()->withCommands()->create(['referrer_name' => 'Kakamora']);
    StoreCommand::factory()->forOwner($referral)->create(['command' => 'say thanks {REFERRER_NAME}']);

    $order = referralCommandOrder($referral, [StorePackage::factory()->create()]);

    (new ProcessStoreOrderPurchaseJob($order))->handle(
        app(StoreCommandDispatchService::class),
        app(StoreOrderService::class),
        app(StoreGiftCardService::class),
    );

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toBe('say thanks Kakamora');
});

<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageType;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Jobs\Store\ProcessStoreOrderRevocationJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
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
    // RunCommandQueueJob opens a real socket to the factory server's host with a 10s connect
    // timeout, and QUEUE_CONNECTION is sync in tests. The rows it would read are written before
    // dispatch, so every assertion below still holds.
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * A paid order whose lines were priced under $sale.
 *
 * @param  array<int, StorePackage>  $packages
 */
function saleCommandPaidOrder(array $packages, ?StoreSale $sale = null, int $quantity = 1): StoreOrder
{
    $order = StoreOrder::factory()->paid()->create([
        'player_username' => 'Steve',
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
    ]);

    foreach ($packages as $package) {
        $order->items()->create([
            'store_package_id' => $package->id,
            'package_name' => $package->name,
            'quantity' => $quantity,
            'unit_price_original' => 1000,
            'unit_price' => 900,
            'total' => 900 * $quantity,
            'store_sale_id' => $sale?->id,
            'sale_name' => $sale?->name,
            'expiry_duration_days' => $package->expiry_duration_days,
        ]);
    }

    return $order->fresh();
}

/**
 * A command owned by a sale rather than a package.
 *
 * Passing no packages leaves it on "every package this sale discounts", which is what an admin
 * gets by leaving the picker empty.
 *
 * @param  array<int, StorePackage>|null  $packages
 * @param  array<int, Server>|null  $servers
 */
function saleCommand(StoreSale $sale, array $attributes = [], ?array $packages = null, ?array $servers = null): StorePackageCommand
{
    $command = StorePackageCommand::factory()->forSale($sale)->create(array_merge([
        'trigger' => StorePackageCommandTrigger::PURCHASE,
        'command' => 'give {PLAYER_USERNAME} coins 100',
        'is_run_on_all_servers' => $servers === null,
        'is_run_on_all_packages' => $packages === null,
    ], $attributes));

    if ($servers !== null) {
        $command->servers()->sync(collect($servers)->pluck('id')->all());
    }

    if ($packages !== null) {
        $command->packages()->sync(collect($packages)->pluck('id')->all());
    }

    return $command->fresh(['servers', 'packages']);
}

function saleCommandRunPurchase(StoreOrder $order): void
{
    (new ProcessStoreOrderPurchaseJob($order))->handle(
        app(StoreCommandDispatchService::class),
        app(StoreOrderService::class),
        app(StoreGiftCardService::class),
    );
}

function saleCommandParsed(): array
{
    return CommandQueue::where('tag', 'store')->pluck('parsed_command')->sort()->values()->all();
}

test('two sale commands give different packages different amounts', function () {
    Server::factory()->create();
    $starter = StorePackage::factory()->create(['price' => 1000, 'name' => 'Starter']);
    $ultimate = StorePackage::factory()->create(['price' => 1000, 'name' => 'Ultimate']);
    $sale = StoreSale::factory()->create(['name' => 'Bonus Coins']);

    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100'], [$starter]);
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 1000'], [$ultimate]);

    saleCommandRunPurchase(saleCommandPaidOrder([$starter, $ultimate], $sale));

    expect(saleCommandParsed())->toEqual([
        'give Steve coins 100',
        'give Steve coins 1000',
    ]);
});

test('a sale command does not run for a package it does not name', function () {
    Server::factory()->create();
    $named = StorePackage::factory()->create(['price' => 1000]);
    $other = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();

    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100'], [$named]);

    saleCommandRunPurchase(saleCommandPaidOrder([$other], $sale));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(0);
});

test('a sale command with no packages picked runs for every package the sale priced', function () {
    Server::factory()->create();
    $first = StorePackage::factory()->create(['price' => 1000]);
    $second = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();

    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 50']);

    saleCommandRunPurchase(saleCommandPaidOrder([$first, $second], $sale));

    // One per line: both packages were bought under the sale, so both earned the bonus.
    expect(CommandQueue::where('tag', 'store')->count())->toBe(2);
});

test('an item bought without the sale runs no sale command', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();

    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100']);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], null));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(0);
});

test('a package own commands and its sale commands both run', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();

    $own = StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'trigger' => StorePackageCommandTrigger::PURCHASE,
        'command' => 'lp user {PLAYER_USERNAME} parent add vip',
    ]);
    $bonus = saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100']);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(saleCommandParsed())->toEqual([
        'give Steve coins 100',
        'lp user Steve parent add vip',
    ]);
    expect(StoreOrderDelivery::pluck('store_package_command_id')->sort()->values()->all())
        ->toEqual(collect([$own->id, $bonus->id])->sort()->values()->all());
});

test('rerunning the job does not deliver a sale command twice', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100']);

    $order = saleCommandPaidOrder([$package], $sale);
    saleCommandRunPurchase($order);

    $deliveries = StoreOrderDelivery::count();
    $queued = CommandQueue::where('tag', 'store')->count();

    // A webhook replay, or an admin retrying a delivery, must not hand the bonus out again. The
    // unique index on store_order_deliveries is what stops it, and it can only do that because a
    // sale command carries a real store_package_command_id rather than a null.
    saleCommandRunPurchase($order->fresh());

    expect(StoreOrderDelivery::count())->toBe($deliveries);
    expect(CommandQueue::where('tag', 'store')->count())->toBe($queued);
});

test('the sale name and id are substituted into a sale command', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create(['name' => 'Summer Sale']);
    saleCommand($sale, ['command' => 'broadcast {PLAYER_USERNAME} bought under {SALE_NAME} #{SALE_ID}']);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))
        ->toEqual("broadcast Steve bought under Summer Sale #{$sale->id}");
});

test('a sale placeholder on a line with no sale leaves no braces behind', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);

    StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'trigger' => StorePackageCommandTrigger::PURCHASE,
        'command' => 'broadcast {PLAYER_USERNAME} {SALE_NAME}',
    ]);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], null));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toEqual('broadcast Steve ');
});

test('a sale refund command still runs after the sale has ended', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->expired()->create();
    saleCommand($sale, [
        'trigger' => StorePackageCommandTrigger::REFUND,
        'command' => 'take {PLAYER_USERNAME} coins 100',
    ]);

    $order = saleCommandPaidOrder([$package], $sale);
    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
        ->handle(app(StoreCommandDispatchService::class));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toEqual('take Steve coins 100');
});

test('a sale refund command still runs after the sale has been disabled', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create(['is_enabled' => false]);
    saleCommand($sale, [
        'trigger' => StorePackageCommandTrigger::REFUND,
        'command' => 'take {PLAYER_USERNAME} coins 100',
    ]);

    $order = saleCommandPaidOrder([$package], $sale);
    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
        ->handle(app(StoreCommandDispatchService::class));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toEqual('take Steve coins 100');
});

test('a sale refund command still runs after the sale has been deleted', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, [
        'trigger' => StorePackageCommandTrigger::REFUND,
        'command' => 'take {PLAYER_USERNAME} coins 100',
    ]);

    $order = saleCommandPaidOrder([$package], $sale);

    // This is what soft deletes buy: an admin retiring a finished sale must not strand the orders
    // it priced with a bonus that can never be taken back.
    $sale->delete();

    (new ProcessStoreOrderRevocationJob($order->fresh(), StorePackageCommandTrigger::REFUND))
        ->handle(app(StoreCommandDispatchService::class));

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toEqual('take Steve coins 100');
});

test('a repeat per quantity sale command creates one row per unit', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, [
        'command' => 'give {PLAYER_USERNAME} key 1',
        'is_repeat_per_quantity' => true,
    ]);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale, quantity: 3));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(3);
});

test('a sale command runs only on the servers it names', function () {
    $target = Server::factory()->create();
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100'], null, [$target]);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(CommandQueue::where('tag', 'store')->pluck('server_id')->all())->toEqual([$target->id]);
});

test('a sale command with no servers picked runs on every server', function () {
    Server::factory()->count(2)->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100']);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(2);
});

test('a giftcard only package runs no sale commands', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create([
        'price' => 1000,
        'type' => StorePackageType::GIFTCARD,
        'gift_card_amount' => 1000,
    ]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100']);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(CommandQueue::where('tag', 'store')->count())->toBe(0);
});

test('a soft deleted package still matches a sale command that names it', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100'], [$package]);

    $order = saleCommandPaidOrder([$package], $sale);
    $package->delete();

    saleCommandRunPurchase($order->fresh());

    expect(CommandQueue::where('tag', 'store')->value('parsed_command'))->toEqual('give Steve coins 100');
});

test('a sale command scoped to a package does not widen to every package', function () {
    $named = StorePackage::factory()->create(['price' => 1000]);
    $other = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    $command = saleCommand($sale, [], [$named]);

    // The flag is what says "all", not an empty relation. If the pivot rows ever dropped away, an
    // inferring implementation would silently hand the bonus to the whole catalogue.
    $command->packages()->detach();

    expect($command->fresh('packages')->appliesToPackage($other->id))->toBeFalse();
    expect($command->fresh('packages')->appliesToPackage($named->id))->toBeFalse();
});

test('a store command must belong to exactly one owner', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();

    expect(fn () => StorePackageCommand::factory()->create([
        'store_package_id' => $package->id,
        'store_sale_id' => $sale->id,
    ]))->toThrow(LogicException::class);

    expect(fn () => StorePackageCommand::factory()->create([
        'store_package_id' => null,
        'store_sale_id' => null,
    ]))->toThrow(LogicException::class);
});

test('a delayed sale command is left for the sweeper rather than dispatched now', function () {
    Server::factory()->create();
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create();
    saleCommand($sale, ['command' => 'give {PLAYER_USERNAME} coins 100', 'delay_seconds' => 600]);

    saleCommandRunPurchase(saleCommandPaidOrder([$package], $sale));

    expect(CommandQueue::where('tag', 'store')->value('execute_at'))->not->toBeNull();
    Queue::assertNotPushed(RunCommandQueueJob::class);
});

test('an order priced under a sale is recorded as such', function () {
    $package = StorePackage::factory()->create(['price' => 1000]);
    $sale = StoreSale::factory()->create(['name' => 'Summer Sale']);
    $order = saleCommandPaidOrder([$package], $sale);

    expect($order->items->first()->sale->id)->toBe($sale->id);
    expect($order->status)->toEqual(StoreOrderStatus::PAID);
});

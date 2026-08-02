<?php

use App\Enums\CommandQueueStatus;
use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageGrantStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ProcessStoreOrderPurchaseJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreCommand;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StorePayment;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreGiftCardService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * @return array{0: StoreOrder, 1: StorePackage, 2: Server}
 */
function deliveryJobPaidOrder(array $packageAttributes = [], int $quantity = 1): array
{
    $server = Server::factory()->create();
    $package = StorePackage::factory()->create(array_merge(['price' => 1000], $packageAttributes));

    $order = StoreOrder::factory()->paid()->create([
        'player_username' => 'Steve',
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
    ]);
    $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => $quantity,
        'unit_price_original' => 1000,
        'unit_price' => 1000,
        'total' => 1000 * $quantity,
        'expiry_duration_days' => $package->expiry_duration_days,
    ]);

    return [$order->fresh(), $package, $server];
}

/**
 * A purchase command pinned to specific servers. Passing none leaves it on "all servers",
 * which is the default an admin gets by leaving the picker empty.
 *
 * @param  array<int, Server>|null  $servers
 */
function purchaseCommand(StorePackage $package, array $attributes = [], ?array $servers = null): StoreCommand
{
    $command = StoreCommand::factory()->forOwner($package)->create(array_merge([
        'trigger' => StoreCommandTrigger::PURCHASE,
        'command' => 'lp user {PLAYER_USERNAME} parent add vip',
        'is_run_on_all_servers' => $servers === null,
    ], $attributes));

    if ($servers !== null) {
        $command->servers()->sync(collect($servers)->pluck('id')->all());
    }

    return $command->fresh('servers');
}

function deliveryJobRunJob(StoreOrder $order): void
{
    (new ProcessStoreOrderPurchaseJob($order))->handle(
        app(StoreCommandDispatchService::class),
        app(StoreOrderService::class),
        app(StoreGiftCardService::class),
    );
}

test('a paid order queues its purchase commands', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);

    deliveryJobRunJob($order);

    $queue = CommandQueue::where('tag', 'store')->first();
    expect($queue)->not->toBeNull();
    expect($queue->parsed_command)->toEqual('lp user Steve parent add vip');
    expect($queue->status)->toEqual(CommandQueueStatus::PENDING);
    Queue::assertPushed(RunCommandQueueJob::class);
});

test('the queue row always carries the online flag in config', function () {
    // RunCommandQueueJob reads config[is_player_online_required] directly; a row without it
    // used to throw.
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package, ['is_player_online_required' => true]);

    deliveryJobRunJob($order);

    $config = CommandQueue::where('tag', 'store')->first()->config;
    expect($config)->toHaveKey('is_player_online_required');
    expect($config['is_player_online_required'])->toBeTrue();
});

test('max attempts allows the sweeper to retry', function () {
    // Every pre-existing caller uses 1, which means the sweeper never retries anything.
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);

    deliveryJobRunJob($order);

    expect(CommandQueue::where('tag', 'store')->first()->max_attempts)->toBeGreaterThan(1);
});

test('each command carries its own online requirement', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package, ['command' => 'needs online', 'is_player_online_required' => true]);
    purchaseCommand($package, ['command' => 'runs anyway', 'is_player_online_required' => false]);

    deliveryJobRunJob($order);

    $byCommand = CommandQueue::where('tag', 'store')->get()->keyBy('parsed_command');

    expect($byCommand['needs online']->config['is_player_online_required'])->toBeTrue();
    expect($byCommand['runs anyway']->config['is_player_online_required'])->toBeFalse();
});

test('all the documented placeholders are substituted', function () {
    [$order, $package] = deliveryJobPaidOrder([], 3);
    purchaseCommand($package, [
        'command' => 'give {PLAYER_USERNAME} {PLAYER_UUID} {QUANTITY} {PACKAGE_NAME} {ORDER_UUID}',
    ]);

    deliveryJobRunJob($order);

    $parsed = CommandQueue::where('tag', 'store')->first()->parsed_command;
    $this->assertStringContainsString('Steve', $parsed);
    $this->assertStringContainsString('069a79f4-44e9-4726-a5be-fca90e38aaf5', $parsed);
    $this->assertStringContainsString('3', $parsed);
    $this->assertStringContainsString($package->name, $parsed);
    $this->assertStringContainsString($order->uuid, $parsed);
    $this->assertStringNotContainsString('{', $parsed, 'No placeholder should survive substitution.');
});

test('quantity substitution by default', function () {
    [$order, $package] = deliveryJobPaidOrder([], 5);
    purchaseCommand($package, [
        'command' => 'give {PLAYER_USERNAME} diamond {QUANTITY}',
        'is_repeat_per_quantity' => false,
    ]);

    deliveryJobRunJob($order);

    expect(CommandQueue::where('tag', 'store')->count())->toEqual(1);
    expect(CommandQueue::first()->parsed_command)->toEqual('give Steve diamond 5');
});

test('repeat per quantity creates one row per unit', function () {
    // Crate keys and similar: the command has to run N times rather than take a count.
    [$order, $package] = deliveryJobPaidOrder([], 3);
    purchaseCommand($package, [
        'command' => 'crate give {PLAYER_USERNAME} vote {QUANTITY}',
        'is_repeat_per_quantity' => true,
    ]);

    deliveryJobRunJob($order);

    $queues = CommandQueue::where('tag', 'store')->get();
    expect($queues)->toHaveCount(3);

    // Each run is for a single unit, so QUANTITY is 1 rather than 3.
    expect($queues->first()->parsed_command)->toEqual('crate give Steve vote 1');
});

test('a delayed command sets execute at and is left for the sweeper', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package, ['delay_seconds' => 600]);

    deliveryJobRunJob($order);

    $queue = CommandQueue::where('tag', 'store')->first();
    expect($queue->execute_at)->not->toBeNull();
    expect($queue->status)->toEqual(CommandQueueStatus::PENDING);
    Queue::assertNotPushed(RunCommandQueueJob::class);
});

test('a command with no servers picked runs on every server', function () {
    // Leaving the picker empty means all servers, so one added later is included too.
    [$order, $package] = deliveryJobPaidOrder();
    Server::factory()->count(2)->create();
    purchaseCommand($package);

    deliveryJobRunJob($order);

    expect(CommandQueue::where('tag', 'store')->count())->toEqual(3);
});

test('a command runs only on the servers it names', function () {
    [$order, $package, $server] = deliveryJobPaidOrder();
    $other = Server::factory()->create();
    purchaseCommand($package, [], [$server]);

    deliveryJobRunJob($order);

    $queues = CommandQueue::where('tag', 'store')->get();
    expect($queues)->toHaveCount(1);
    expect($queues->first()->server_id)->toEqual($server->id);
    $this->assertNotEquals($other->id, $queues->first()->server_id);
});

test('two commands on one package can target different servers', function () {
    [$order, $package, $server] = deliveryJobPaidOrder();
    $other = Server::factory()->create();

    purchaseCommand($package, ['command' => 'first'], [$server]);
    purchaseCommand($package, ['command' => 'second'], [$other]);

    deliveryJobRunJob($order);

    $byCommand = CommandQueue::where('tag', 'store')->get()->keyBy('parsed_command');

    expect($byCommand['first']->server_id)->toEqual($server->id);
    expect($byCommand['second']->server_id)->toEqual($other->id);
});

test('servers without a webquery port are excluded', function () {
    [$order, $package] = deliveryJobPaidOrder();
    Server::factory()->create(['webquery_port' => null]);
    purchaseCommand($package);

    deliveryJobRunJob($order);

    // A server with no webquery port could never receive the command.
    expect(CommandQueue::where('tag', 'store')->count())->toEqual(1);
});

test('only the matching trigger is dispatched', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);
    StoreCommand::factory()->expiry()->forOwner($package)->create();
    StoreCommand::factory()->refund()->forOwner($package)->create();

    deliveryJobRunJob($order);

    expect(CommandQueue::where('tag', 'store')->count())->toEqual(1);
});

test('rerunning the job does not deliver twice', function () {
    // The whole reason store_order_deliveries carries a unique index: a webhook replay or an
    // admin "retry all failed" must not hand out the purchase again.
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);

    deliveryJobRunJob($order);
    deliveryJobRunJob($order->fresh());

    expect(CommandQueue::where('tag', 'store')->count())->toEqual(1);
    expect(StoreOrderDelivery::count())->toEqual(1);
});

test('a delivery row records what was queued', function () {
    [$order, $package, $server] = deliveryJobPaidOrder();
    $command = purchaseCommand($package);

    deliveryJobRunJob($order);

    $delivery = StoreOrderDelivery::first();
    expect($delivery->store_order_id)->toEqual($order->id);
    expect($delivery->store_command_id)->toEqual($command->id);
    expect($delivery->server_id)->toEqual($server->id);
    expect($delivery->trigger)->toEqual(StoreCommandTrigger::PURCHASE);
    expect($delivery->parsed_command)->toEqual('lp user Steve parent add vip');
    expect($delivery->commandQueue)->not->toBeNull();
});

test('a grant is issued per item', function () {
    [$order, $package] = deliveryJobPaidOrder(['expiry_duration_days' => 30]);
    purchaseCommand($package);

    deliveryJobRunJob($order);

    $grant = $order->items->first()->grant;
    expect($grant->status)->toEqual(StorePackageGrantStatus::ACTIVE);
    expect($grant->expires_at)->not->toBeNull();
    expect(now()->diffInDays($grant->expires_at))->toEqualWithDelta(30, 1);
});

test('a permanent package grant has no expiry', function () {
    [$order, $package] = deliveryJobPaidOrder(['expiry_duration_days' => null]);
    purchaseCommand($package);

    deliveryJobRunJob($order);

    expect($order->items->first()->grant->expires_at)->toBeNull();
});

test('sold count is incremented only once when paid', function () {
    [$order, $package] = deliveryJobPaidOrder([], 2);
    purchaseCommand($package);

    deliveryJobRunJob($order);
    expect($package->fresh()->sold_count)->toEqual(2);

    // A retry must not inflate stock consumption.
    deliveryJobRunJob($order->fresh());
    expect($package->fresh()->sold_count)->toEqual(2);
});

test('the order is completed after delivery is queued', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);

    deliveryJobRunJob($order);

    $order->refresh();
    expect($order->status)->toEqual(StoreOrderStatus::COMPLETED);
    expect($order->delivery_status)->toEqual(StoreDeliveryStatus::PENDING);
});

test('a package with no commands still completes the order', function () {
    [$order] = deliveryJobPaidOrder();

    deliveryJobRunJob($order);

    $order->refresh();
    expect($order->status)->toEqual(StoreOrderStatus::COMPLETED);
    expect($order->delivery_status)->toEqual(StoreDeliveryStatus::DELIVERED, 'Nothing to deliver counts as delivered.');
});

test('delivery is marked failed when no server can receive it', function () {
    $package = StorePackage::factory()->create();
    Server::factory()->create(['webquery_port' => null]);
    $order = StoreOrder::factory()->paid()->create();
    $order->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 1000, 'unit_price' => 1000, 'total' => 1000,
    ]);
    purchaseCommand($package);

    deliveryJobRunJob($order->fresh());

    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::FAILED);
});

test('a refunded order is not delivered', function () {
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);
    $order->update(['status' => StoreOrderStatus::REFUNDED]);

    deliveryJobRunJob($order->fresh());

    expect(CommandQueue::where('tag', 'store')->count())->toEqual(0);
});

test('paying an order dispatches the fulfilment job', function () {
    Queue::fake();
    [$order, $package] = deliveryJobPaidOrder();
    purchaseCommand($package);

    $pending = StoreOrder::factory()->create(['total' => 1000, 'amount_due' => 1000]);
    $payment = StorePayment::factory()->create([
        'store_order_id' => $pending->id, 'amount' => 1000, 'currency' => 'USD',
    ]);

    app(StoreOrderService::class)->markPaid($pending, $payment, 1000, 'USD');

    Queue::assertPushed(ProcessStoreOrderPurchaseJob::class);
});

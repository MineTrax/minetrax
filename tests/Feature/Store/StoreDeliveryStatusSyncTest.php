<?php

use App\Enums\CommandQueueStatus;
use App\Enums\StoreCommandTrigger;
use App\Enums\StoreDeliveryStatus;
use App\Jobs\RunCommandQueueJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    // Answered locally, so RunCommandQueueJob can run for real instead of opening a socket to a
    // server that is not there and waiting 10s for it.
    config(['minetrax.webquery.fake' => true, 'minetrax.webquery.fake_fail_rate' => 0]);
});

/**
 * A completed order still waiting on its purchase commands, exactly as markCompleted() leaves it.
 */
function syncTestOrder(): StoreOrder
{
    return StoreOrder::factory()->completed()->create([
        'delivery_status' => StoreDeliveryStatus::PENDING,
        'player_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
    ]);
}

/**
 * One purchase delivery with its queue row, in the given state.
 *
 * @param  array<string, mixed>  $queueAttributes
 */
function syncTestDelivery(
    StoreOrder $order,
    CommandQueueStatus $status = CommandQueueStatus::PENDING,
    array $queueAttributes = [],
    StoreCommandTrigger $trigger = StoreCommandTrigger::PURCHASE,
): StoreOrderDelivery {
    $package = StorePackage::factory()->create();
    $item = $order->items()->create([
        'store_package_id' => $package->id, 'package_name' => $package->name, 'quantity' => 1,
        'unit_price_original' => 100, 'unit_price' => 100, 'total' => 100,
    ]);

    $queue = CommandQueue::create(array_merge([
        'server_id' => Server::factory()->create()->id,
        'parsed_command' => 'give Steve diamond 1',
        'config' => ['is_player_online_required' => false],
        'status' => $status,
        'max_attempts' => 3,
        'attempts' => 0,
        'tag' => 'store',
        'player_uuid' => $order->player_uuid,
    ], $queueAttributes));

    return StoreOrderDelivery::create([
        'store_order_id' => $order->id,
        'store_order_item_id' => $item->id,
        'store_command_id' => null,
        'server_id' => $queue->server_id,
        'command_queue_id' => $queue->id,
        'trigger' => $trigger,
        'parsed_command' => $queue->parsed_command,
        'repeat_index' => 0,
    ]);
}

// --- Running the job for real, with the fake transport -------------------------------------

test('a faked command run completes the row and delivers the order', function () {
    $order = syncTestOrder();
    $delivery = syncTestDelivery($order);

    (new RunCommandQueueJob($delivery->commandQueue))->handle();

    $queue = $delivery->commandQueue->fresh();
    expect($queue->status)->toEqual(CommandQueueStatus::COMPLETED);
    expect($queue->attempts)->toEqual(1);
    // Stored as text and marked, so a stored output makes the fake obvious in hindsight.
    expect($queue->output)->toContain('"faked":true');

    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::DELIVERED);
});

test('the order stays pending until the last of its commands lands', function () {
    $order = syncTestOrder();
    $first = syncTestDelivery($order);
    $second = syncTestDelivery($order);

    (new RunCommandQueueJob($first->commandQueue))->handle();
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::PENDING);

    (new RunCommandQueueJob($second->commandQueue))->handle();
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::DELIVERED);
});

test('delivering writes one history line, not one per command', function () {
    $order = syncTestOrder();
    $first = syncTestDelivery($order);
    $second = syncTestDelivery($order);

    (new RunCommandQueueJob($first->commandQueue))->handle();
    (new RunCommandQueueJob($second->commandQueue))->handle();

    $lines = Activity::inLog(StoreOrderService::ACTIVITY_LOG)
        ->where('subject_id', $order->id)
        ->where('event', 'delivery_delivered')
        ->get();

    expect($lines)->toHaveCount(1);
    expect($lines->first()->properties['completed'])->toEqual(2);
    expect($lines->first()->properties['total'])->toEqual(2);
});

test('a command waiting on the player leaves the order pending and says so', function () {
    config(['minetrax.webquery.fake_player_online' => false]);

    $order = syncTestOrder();
    $delivery = syncTestDelivery($order, queueAttributes: ['config' => ['is_player_online_required' => true]]);

    (new RunCommandQueueJob($delivery->commandQueue))->handle();

    expect($delivery->commandQueue->fresh()->status)->toEqual(CommandQueueStatus::DEFERRED);
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::PENDING);

    $summary = app(StoreOrderService::class)->deliverySummary($order->fresh());
    expect($summary['waiting_for_player'])->toBeTrue();
    expect($summary['in_progress'])->toEqual(1);
});

test('a faked failure is a real failure to the queue', function () {
    config(['minetrax.webquery.fake_fail_rate' => 100]);

    $order = syncTestOrder();
    $delivery = syncTestDelivery($order);

    (new RunCommandQueueJob($delivery->commandQueue))->handle();

    $queue = $delivery->commandQueue->fresh();
    expect($queue->status)->toEqual(CommandQueueStatus::FAILED);
    expect($queue->output)->toContain('(faked)');
    // One attempt of three: the sweeper will retry it, so the buyer is still told "delivering".
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::PENDING);
});

test('the fake is ignored in production', function () {
    $this->app['env'] = 'production';

    // Nothing listens on port 1, so a real attempt is refused at once rather than timing out.
    $server = Server::factory()->create(['ip_address' => '127.0.0.1', 'webquery_port' => 1]);
    $order = syncTestOrder();
    $delivery = syncTestDelivery($order, queueAttributes: ['server_id' => $server->id]);

    (new RunCommandQueueJob($delivery->commandQueue))->handle();

    $queue = $delivery->commandQueue->fresh();
    expect($queue->status)->toEqual(CommandQueueStatus::FAILED);
    expect($queue->output)->not->toContain('faked');
});

// --- Deriving the status from the rows -----------------------------------------------------

test('a failure out of attempts fails the order', function () {
    $order = syncTestOrder();
    syncTestDelivery($order, CommandQueueStatus::FAILED, ['attempts' => 3]);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::FAILED);
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::FAILED);
});

test('a failure with attempts left is still in progress', function () {
    $order = syncTestOrder();
    syncTestDelivery($order, CommandQueueStatus::FAILED, ['attempts' => 1]);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::PENDING);
});

test('a cancelled row counts as failed', function () {
    // Cancelled is what the job writes when the server has no webquery port; nothing retries it.
    $order = syncTestOrder();
    syncTestDelivery($order, CommandQueueStatus::CANCELLED);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::FAILED);
});

test('some delivered and some failed is partial', function () {
    $order = syncTestOrder();
    syncTestDelivery($order, CommandQueueStatus::COMPLETED);
    syncTestDelivery($order, CommandQueueStatus::FAILED, ['attempts' => 3]);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::PARTIAL);

    expect(Activity::inLog(StoreOrderService::ACTIVITY_LOG)
        ->where('subject_id', $order->id)
        ->where('event', 'delivery_partial')
        ->exists())->toBeTrue();
});

test('refund commands do not count against delivery', function () {
    $order = syncTestOrder();
    syncTestDelivery($order, CommandQueueStatus::COMPLETED);
    syncTestDelivery($order, CommandQueueStatus::FAILED, ['attempts' => 3], StoreCommandTrigger::REFUND);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::DELIVERED);
});

test('an order with nothing queued is left alone', function () {
    $order = StoreOrder::factory()->completed()->create(['delivery_status' => StoreDeliveryStatus::DELIVERED]);

    expect(app(StoreOrderService::class)->syncDeliveryStatus($order))->toEqual(StoreDeliveryStatus::DELIVERED);
});

test('a queue row that is not a store delivery touches no order', function () {
    $order = syncTestOrder();
    $queue = CommandQueue::factory()->create(['tag' => 'run_command']);

    (new RunCommandQueueJob($queue))->handle();

    expect($queue->fresh()->status)->toEqual(CommandQueueStatus::COMPLETED);
    expect($order->fresh()->delivery_status)->toEqual(StoreDeliveryStatus::PENDING);
});

// --- What the result page polls ------------------------------------------------------------

test('the status endpoint carries the per command summary', function () {
    $order = syncTestOrder();
    $order->update(['user_id' => null]);
    syncTestDelivery($order, CommandQueueStatus::COMPLETED);
    syncTestDelivery($order, CommandQueueStatus::DEFERRED);

    $this->getJson(route('store.order.status', $order->uuid))
        ->assertOk()
        ->assertJson([
            'status' => 'completed',
            'delivery_status' => 'pending',
            'delivery' => [
                'total' => 2,
                'completed' => 1,
                'in_progress' => 1,
                'failed' => 0,
                'waiting_for_player' => true,
            ],
        ]);
});

test('the result page carries the same summary and the money breakdown', function () {
    $order = syncTestOrder();
    $order->update(['user_id' => null, 'subtotal' => 2000, 'coupon_discount' => 100, 'total' => 1900, 'amount_due' => 1900]);
    syncTestDelivery($order, CommandQueueStatus::COMPLETED);

    $this->get(route('store.order.result', $order->uuid))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Store/ResultStoreOrder')
            ->where('order.delivery.total', 1)
            ->where('order.delivery.completed', 1)
            ->where('order.raw.coupon_discount', 100)
            ->has('order.money.subtotal')
            ->has('order.money.coupon_discount')
            ->has('order.number')
        );
});

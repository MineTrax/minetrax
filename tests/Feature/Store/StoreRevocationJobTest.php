<?php

use App\Enums\StoreOrderStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Enums\StorePackageGrantStatus;
use App\Enums\StorePaymentStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\Store\ProcessStoreOrderRevocationJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StorePayment;
use App\Services\StoreCommandDispatchService;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['store.enabled' => true]);
    $this->baseCurrency();

    Server::factory()->create();

    // The queue is sync in tests, so a dispatched command would open a real socket to the
    // factory server and block for the 10s webquery timeout. Every assertion here is about
    // the command_queues rows, which are written before dispatch.
    Queue::fake([RunCommandQueueJob::class]);
});

/**
 * A paid order for a package carrying both revocation command sets.
 */
function revocationJobPaidOrder(): StoreOrder
{
    $package = StorePackage::factory()->create(['price' => 1000]);

    foreach ([StorePackageCommandTrigger::REFUND, StorePackageCommandTrigger::CHARGEBACK] as $trigger) {
        StorePackageCommand::factory()->create([
            'store_package_id' => $package->id,
            'trigger' => $trigger,
            'command' => 'lp user {PLAYER_USERNAME} parent remove vip # '.$trigger->value,
        ]);
    }

    $order = StoreOrder::factory()->completed()->create(['total' => 1000, 'amount_due' => 1000]);
    $item = $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 1000,
        'unit_price' => 1000,
        'total' => 1000,
    ]);
    $item->grant()->create([
        'store_package_id' => $package->id,
        'player_uuid' => $order->player_uuid,
        'status' => StorePackageGrantStatus::ACTIVE,
        'granted_at' => now(),
    ]);

    StorePayment::factory()->create([
        'store_order_id' => $order->id,
        'amount' => 1000,
        'currency' => $order->currency,
        'status' => StorePaymentStatus::COMPLETED,
    ]);

    return $order->fresh();
}

/**
 * @return array<int, string>
 */
function queuedCommands(): array
{
    return CommandQueue::where('tag', 'store')->pluck('parsed_command')->all();
}

test('the job queues the refund commands', function () {
    $order = revocationJobPaidOrder();

    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
        ->handle(app(StoreCommandDispatchService::class));

    expect(queuedCommands())->toBe(['lp user '.$order->player_username.' parent remove vip # refund']);
    $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'refund']);
});

test('the job queues the chargeback commands', function () {
    $order = revocationJobPaidOrder();

    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::CHARGEBACK))
        ->handle(app(StoreCommandDispatchService::class));

    expect(queuedCommands())->toBe(['lp user '.$order->player_username.' parent remove vip # chargeback']);
    $this->assertDatabaseHas('store_order_deliveries', ['trigger' => 'chargeback']);
});

test('re running the job queues nothing further', function () {
    // The unique index on store_order_deliveries is what makes a retry free.
    $order = revocationJobPaidOrder();
    $dispatcher = app(StoreCommandDispatchService::class);

    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))->handle($dispatcher);
    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))->handle($dispatcher);

    expect(queuedCommands())->toHaveCount(1);
});

test('the job leaves the orders delivery status alone', function () {
    // delivery_status describes how the purchase went out, not how it was taken back.
    $order = revocationJobPaidOrder();
    $before = $order->delivery_status;

    (new ProcessStoreOrderRevocationJob($order, StorePackageCommandTrigger::REFUND))
        ->handle(app(StoreCommandDispatchService::class));

    expect($order->fresh()->delivery_status)->toEqual($before);
});

test('a full refund dispatches the revocation', function () {
    Queue::fake([ProcessStoreOrderRevocationJob::class]);

    $order = revocationJobPaidOrder();

    app(StoreOrderService::class)->refund($order, 1000);

    Queue::assertPushed(ProcessStoreOrderRevocationJob::class);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::REFUNDED);
});

test('a chargeback dispatches the revocation', function () {
    Queue::fake([ProcessStoreOrderRevocationJob::class]);

    $order = revocationJobPaidOrder();

    app(StoreOrderService::class)->refund($order, 1000, isChargeback: true);

    Queue::assertPushed(ProcessStoreOrderRevocationJob::class);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::CHARGEBACK);
});

test('a partial refund dispatches nothing', function () {
    Queue::fake([ProcessStoreOrderRevocationJob::class]);

    $order = revocationJobPaidOrder();

    app(StoreOrderService::class)->refund($order, 400);

    Queue::assertNotPushed(ProcessStoreOrderRevocationJob::class);
    expect($order->fresh()->status)->toEqual(StoreOrderStatus::PARTIALLY_REFUNDED);
    expect($order->items->first()->grant->fresh()->status)->toEqual(StorePackageGrantStatus::ACTIVE, 'A partial refund leaves the grant active.');
});

test('a refund the state machine rejects dispatches nothing', function () {
    Queue::fake([ProcessStoreOrderRevocationJob::class]);

    $order = revocationJobPaidOrder();
    $order->update(['status' => StoreOrderStatus::CANCELLED]);

    expect(app(StoreOrderService::class)->refund($order->fresh(), 1000))->toBeFalse();

    Queue::assertNotPushed(ProcessStoreOrderRevocationJob::class);
});

test('a chargeback runs the chargeback commands not the refund ones', function () {
    // The trigger the listener picks is only observable in what actually gets queued.
    $order = revocationJobPaidOrder();

    app(StoreOrderService::class)->refund($order, 1000, isChargeback: true);

    expect(queuedCommands())->toBe(['lp user '.$order->player_username.' parent remove vip # chargeback']);
});

test('the refund commands run end to end without faking the queue', function () {
    // The listener, the job and the dispatcher together — the path a real refund takes.
    $order = revocationJobPaidOrder();

    app(StoreOrderService::class)->refund($order, 1000);

    expect(queuedCommands())->toBe(['lp user '.$order->player_username.' parent remove vip # refund']);
    expect($order->items->first()->grant->fresh()->status)->toEqual(StorePackageGrantStatus::REVOKED);
});

<?php

use App\Enums\StoreCommandTrigger;
use App\Models\Server;
use App\Models\StoreCommand;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StorePackage;
use App\Models\StoreSale;
use App\Models\User;
use App\Traits\HasStoreCommandsTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Every command owner registered in config, as [class => spec].
 *
 * @return array<class-string, array<string, mixed>>
 */
function commandOwnerRegistry(): array
{
    return config('store.command_owners', []);
}

test('the registry is populated, so the rules below are not vacuous', function () {
    // Same guard StoreArchTest puts on its globs: an assertion that iterates an empty list passes
    // for the wrong reason.
    expect(commandOwnerRegistry())->not->toBeEmpty();
    expect(commandOwnerRegistry())->toHaveKey(StorePackage::class);
});

test('every registered command owner can actually own one', function () {
    // Registry-driven on purpose, the same shape as StoreGatewayContractTest: a command owner added
    // later is covered by this test the moment its config line lands, with nothing to remember.
    foreach (commandOwnerRegistry() as $class => $spec) {
        expect(class_exists($class))->toBeTrue("Registered command owner [{$class}] does not exist.");

        expect(in_array(HasStoreCommandsTrait::class, class_uses_recursive($class), true))
            ->toBeTrue("[{$class}] is registered as a command owner but does not use HasStoreCommandsTrait.");

        $relation = (new $class)->commands();

        expect($relation)->toBeInstanceOf(MorphMany::class);
        expect($relation->getRelated())->toBeInstanceOf(StoreCommand::class);

        expect($spec['triggers'] ?? [])->not->toBeEmpty("[{$class}] declares no triggers, so nothing it owns could ever run.");

        foreach ($spec['triggers'] as $trigger) {
            expect(StoreCommandTrigger::tryFrom($trigger))
                ->not->toBeNull("[{$class}] declares trigger [{$trigger}], which is not a StoreCommandTrigger case.");
        }
    }
});

test('a command owner round trips through the morph', function () {
    $package = StorePackage::factory()->create();
    $sale = StoreSale::factory()->create();

    $packageCommand = $package->commands()->create([
        'trigger' => StoreCommandTrigger::PURCHASE,
        'command' => 'give {PLAYER_USERNAME} diamond 1',
    ]);

    $saleCommand = $sale->commands()->create([
        'trigger' => StoreCommandTrigger::PURCHASE,
        'command' => 'give {PLAYER_USERNAME} coins 100',
    ]);

    expect($packageCommand->fresh()->commandable->is($package))->toBeTrue();
    expect($saleCommand->fresh()->commandable->is($sale))->toBeTrue();

    // Each owner sees only its own. A morph shared by three kinds would be worthless if reading it
    // back handed you somebody else's rows.
    expect($package->commands()->pluck('id')->all())->toEqual([$packageCommand->id]);
    expect($sale->commands()->pluck('id')->all())->toEqual([$saleCommand->id]);
});

test('an unregistered owner type is refused rather than stored', function () {
    // A morph column takes any string at all. A command owned by something the dispatcher has never
    // heard of is a row that no admin form can reach and no delivery will ever run, so it is better
    // to fail at the write than to leave one behind.
    expect(fn () => StoreCommand::factory()->create([
        'commandable_type' => User::class,
        'commandable_id' => 1,
    ]))->toThrow(LogicException::class);

    expect(StoreCommand::count())->toBe(0);
});

test('force deleting an owner takes its commands, soft deleting does not', function () {
    // The morph gives up the foreign key that used to cascade, so HasStoreCommandsTrait has to do
    // it. Soft deletes are deliberately spared: a retired sale still has to run the refund that
    // takes its bonus back, months after it stopped discounting anything.
    $sale = StoreSale::factory()->create();
    $command = StoreCommand::factory()->forSale($sale)->create();

    $sale->delete();

    expect(StoreSale::withTrashed()->find($sale->id)->trashed())->toBeTrue();
    expect(StoreCommand::find($command->id))->not->toBeNull();

    $sale->forceDelete();

    expect(StoreCommand::find($command->id))->toBeNull();
});

test('deleting a command keeps the delivery that ran it', function () {
    // store_order_deliveries.store_command_id is nullOnDelete, not cascade. What a buyer was
    // actually sent is part of the order's paper trail and has to outlive the command that produced
    // it — the same reasoning that keeps the row when a server is deleted.
    $server = Server::factory()->create();
    $package = StorePackage::factory()->create();
    $command = StoreCommand::factory()->forOwner($package)->create();

    $order = StoreOrder::factory()->paid()->create();
    $item = $order->items()->create([
        'store_package_id' => $package->id,
        'package_name' => $package->name,
        'quantity' => 1,
        'unit_price_original' => 1000,
        'unit_price' => 1000,
        'total' => 1000,
    ]);

    $delivery = StoreOrderDelivery::create([
        'store_order_id' => $order->id,
        'store_order_item_id' => $item->id,
        'store_command_id' => $command->id,
        'server_id' => $server->id,
        'trigger' => StoreCommandTrigger::PURCHASE,
        'parsed_command' => 'give Steve diamond 1',
        'repeat_index' => 0,
    ]);

    $command->delete();

    $delivery->refresh();

    expect($delivery->exists)->toBeTrue();
    expect($delivery->store_command_id)->toBeNull();
    expect($delivery->parsed_command)->toEqual('give Steve diamond 1');
});

test('every registered owner that soft deletes keeps its commands reachable while trashed', function () {
    // A soft-deleted owner's commands still have to be findable, or the expiry and refund sets it
    // owns would quietly stop running. Asserted over the registry so a future owner cannot opt out
    // of it by accident.
    foreach (commandOwnerRegistry() as $class => $spec) {
        if (! in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            continue;
        }

        $owner = $class::factory()->create();
        $command = StoreCommand::factory()->forOwner($owner)->create();

        $owner->delete();

        expect(StoreCommand::find($command->id))
            ->not->toBeNull("[{$class}] lost its commands to a soft delete.");
    }
});

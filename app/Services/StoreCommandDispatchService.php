<?php

namespace App\Services;

use App\Enums\CommandQueueStatus;
use App\Enums\StoreDeliveryStatus;
use App\Enums\StorePackageCommandTrigger;
use App\Jobs\RunCommandQueueJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\StoreOrder;
use App\Models\StoreOrderDelivery;
use App\Models\StoreOrderItem;
use App\Models\StorePackageCommand;
use App\Utils\Helpers\Helper;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;

/**
 * Turns order items into rows on the existing CommandQueue.
 *
 * Deliberately builds on the pipeline MineTrax already has rather than adding a second one: the
 * "defer until the player is online" behaviour, the retry sweeper and the plugin transport all
 * come for free, which is why the store needs no plugin changes at all.
 */
class StoreCommandDispatchService
{
    /**
     * Dispatch every command for a trigger across the whole order.
     *
     * @return StoreDeliveryStatus the resulting delivery health for the order
     */
    public function dispatchForOrder(StoreOrder $order, StorePackageCommandTrigger $trigger): StoreDeliveryStatus
    {
        $order->loadMissing('items.package.commands.servers');

        $created = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            $result = $this->dispatchForItem($order, $item, $trigger);
            $created += $result['created'];
            $skipped += $result['skipped'];
        }

        return match (true) {
            $created === 0 && $skipped === 0 => StoreDeliveryStatus::DELIVERED, // nothing to deliver
            $created === 0 => StoreDeliveryStatus::FAILED,
            $skipped > 0 => StoreDeliveryStatus::PARTIAL,
            default => StoreDeliveryStatus::PENDING,
        };
    }

    /**
     * @return array{created: int, skipped: int}
     */
    public function dispatchForItem(StoreOrder $order, StoreOrderItem $item, StorePackageCommandTrigger $trigger): array
    {
        $package = $item->package;

        if (! $package) {
            // The package was hard-deleted. Nothing to run, but the order item stays as a record.
            return ['created' => 0, 'skipped' => 0];
        }

        $commands = $package->commands->where('trigger', $trigger);

        if ($commands->isEmpty()) {
            return ['created' => 0, 'skipped' => 0];
        }

        $created = 0;
        $skipped = 0;

        foreach ($commands as $command) {
            $servers = $this->targetServers($command);

            if ($servers->isEmpty()) {
                // Every candidate server lacks a webquery port, so it could never receive this.
                $skipped++;

                continue;
            }

            $repeat = (bool) $command->is_repeat_per_quantity;
            $runs = $repeat ? max(1, (int) $item->quantity) : 1;

            foreach ($servers as $server) {
                for ($index = 0; $index < $runs; $index++) {
                    $delivery = $this->createDelivery($order, $item, $command, $server, $trigger, $index, $repeat);

                    $delivery ? $created++ : $skipped++;
                }
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Re-run deliveries for an order that did not land.
     *
     * A plain re-dispatch would do nothing: the unique index on store_order_deliveries exists
     * precisely so a repeated call cannot double-deliver. So this reuses the existing delivery
     * rows and gives each one a fresh CommandQueue, which keeps one audit record per
     * (item, command, server) however many times an admin retries it.
     *
     * Only deliveries that actually failed are retried by default. One still sitting DEFERRED is
     * waiting on the player to come online and is working as intended.
     *
     * @return int how many deliveries were re-queued
     */
    public function redispatchForOrder(StoreOrder $order, bool $includeUnfinished = false): int
    {
        $order->loadMissing('deliveries.commandQueue', 'deliveries.server');

        $retryable = [CommandQueueStatus::FAILED, CommandQueueStatus::CANCELLED];
        $count = 0;

        foreach ($order->deliveries as $delivery) {
            $status = $delivery->commandQueue?->status;

            $shouldRetry = $status === null
                || in_array($status, $retryable, true)
                || ($includeUnfinished && $status !== CommandQueueStatus::COMPLETED);

            if (! $shouldRetry || ! $delivery->server_id) {
                continue;
            }

            $queue = CommandQueue::create([
                'command_id' => null,
                'server_id' => $delivery->server_id,
                'parsed_command' => $delivery->parsed_command,
                'config' => ['is_player_online_required' => (bool) data_get($delivery->commandQueue?->config, 'is_player_online_required', false)],
                'params' => $delivery->commandQueue?->params,
                'status' => CommandQueueStatus::PENDING,
                'max_attempts' => (int) config('store.command_max_attempts', 3),
                'tag' => 'store',
                'player_uuid' => $order->player_uuid,
                'player_id' => $order->player_id,
                'user_id' => $order->user_id,
            ]);

            $delivery->update([
                'command_queue_id' => $queue->id,
                'redispatch_count' => (int) $delivery->redispatch_count + 1,
            ]);

            RunCommandQueueJob::dispatch($queue);
            $count++;
        }

        return $count;
    }

    /**
     * Create one queue row plus its audit record, or return null when this exact dispatch has
     * already happened.
     */
    private function createDelivery(
        StoreOrder $order,
        StoreOrderItem $item,
        StorePackageCommand $command,
        Server $server,
        StorePackageCommandTrigger $trigger,
        int $repeatIndex,
        bool $repeatPerQuantity,
    ): ?StoreOrderDelivery {
        // The unique index on store_order_deliveries is the real guard. Checking first keeps the
        // common case out of the exception path; the constraint catches the racing case.
        $exists = StoreOrderDelivery::where([
            'store_order_item_id' => $item->id,
            'store_package_command_id' => $command->id,
            'server_id' => $server->id,
            'trigger' => $trigger->value,
            'repeat_index' => $repeatIndex,
        ])->exists();

        if ($exists) {
            return null;
        }

        $params = $this->parametersFor($order, $item, $repeatPerQuantity);
        $parsed = Helper::replacePlaceholders($command->command, $params);

        $delaySeconds = (int) $command->delay_seconds;
        $executeAt = $delaySeconds > 0 ? now()->addSeconds($delaySeconds) : null;

        $isPlayerOnlineRequired = (bool) $command->is_player_online_required;

        try {
            $queue = CommandQueue::create([
                'command_id' => null,
                'server_id' => $server->id,
                'parsed_command' => $parsed,
                // Always set: RunCommandQueueJob reads this key and used to throw without it.
                'config' => ['is_player_online_required' => (bool) $isPlayerOnlineRequired],
                'params' => $params,
                'status' => CommandQueueStatus::PENDING,
                // 3+, not the 1 every other caller uses, so the every-minute sweeper can actually
                // retry a delivery that failed transiently.
                'max_attempts' => (int) config('store.command_max_attempts', 3),
                'execute_at' => $executeAt,
                'tag' => 'store',
                'player_uuid' => $order->player_uuid,
                'player_id' => $order->player_id,
                'user_id' => $order->user_id,
            ]);

            $delivery = StoreOrderDelivery::create([
                'store_order_id' => $order->id,
                'store_order_item_id' => $item->id,
                'store_package_command_id' => $command->id,
                'server_id' => $server->id,
                'command_queue_id' => $queue->id,
                'trigger' => $trigger,
                'parsed_command' => $parsed,
                'repeat_index' => $repeatIndex,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Lost the race with a concurrent dispatch; the other one delivered it.
            return null;
        }

        // A delayed command is left for the every-minute sweeper to pick up when due.
        if (! $executeAt) {
            RunCommandQueueJob::dispatch($queue);
        }

        return $delivery;
    }

    /**
     * Servers this command runs on, filtered to those that can actually receive one.
     *
     * Each command owns its own list. An empty list means every server, so a server added to the
     * network later starts receiving the command without anyone editing the package.
     *
     * @return Collection<int, Server>
     */
    private function targetServers(StorePackageCommand $command): Collection
    {
        if ($command->is_run_on_all_servers || $command->servers->isEmpty()) {
            return Server::whereNotNull('webquery_port')->get();
        }

        return $command->servers->filter(fn (Server $server) => $server->webquery_port !== null)->values();
    }

    /**
     * Placeholder values for a command.
     *
     * Helper::replacePlaceholders needs no changes for the store: it is a plain {key} -> value
     * loop, so this just supplies more keys.
     *
     * @return array<string, mixed>
     */
    private function parametersFor(StoreOrder $order, StoreOrderItem $item, bool $repeatPerQuantity): array
    {
        $params = [
            'player_username' => $order->player_username,
            'player_uuid' => $order->player_uuid,
            // With repeat-per-quantity the command runs N times, so each run is for one unit.
            'quantity' => $repeatPerQuantity ? 1 : (int) $item->quantity,
            'package_name' => $item->package_name,
            'package_id' => $item->store_package_id,
            'order_id' => $order->id,
            'order_uuid' => $order->uuid,
            'currency' => $order->currency,
        ];

        return $params;
    }
}

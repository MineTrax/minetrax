<?php

namespace App\Events;

use App\Models\CommandQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * One attempt at a queue row has settled: completed, failed, deferred or cancelled.
 *
 * Fired by RunCommandQueueJob whatever the outcome, so anything that tracks a row — the store's
 * delivery records, for one — can follow the queue instead of freezing on the state it was
 * queued in.
 */
class CommandQueueRunFinished
{
    use Dispatchable, SerializesModels;

    public function __construct(public CommandQueue $commandQueue) {}
}

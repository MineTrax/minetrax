<?php

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('run command queues from request creates queues for global scope', function () {
    Queue::fake([RunCommandQueueJob::class]);

    $server = Server::factory()->create(['webquery_port' => 25575]);

    $request = collect([
        'scope' => 'global',
        'command' => 'say Hello World',
        'execute_at' => null,
        'servers' => [['id' => $server->id]],
    ]);

    $job = new RunCommandQueuesFromRequestJob($request, 1);
    $job->handle();

    $this->assertDatabaseHas('command_queues', [
        'server_id' => $server->id,
        'parsed_command' => 'say Hello World',
        'status' => CommandQueueStatus::PENDING->value,
        'tag' => 'run_command',
    ]);

    Queue::assertPushed(RunCommandQueueJob::class);
});

test('run command queues defers dispatch when execute at set', function () {
    Queue::fake([RunCommandQueueJob::class]);

    $server = Server::factory()->create(['webquery_port' => 25575]);
    $executeAt = now()->addHour()->toDateTimeString();

    $request = collect([
        'scope' => 'global',
        'command' => 'say Scheduled',
        'execute_at' => $executeAt,
        'servers' => [['id' => $server->id]],
    ]);

    $job = new RunCommandQueuesFromRequestJob($request, 1);
    $job->handle();

    $this->assertDatabaseHas('command_queues', [
        'server_id' => $server->id,
        'parsed_command' => 'say Scheduled',
    ]);

    // When execute_at is set, RunCommandQueueJob should NOT be dispatched immediately
    Queue::assertNotPushed(RunCommandQueueJob::class);
});

test('run command queues uses all webquery servers when none specified', function () {
    Queue::fake([RunCommandQueueJob::class]);

    $server1 = Server::factory()->create(['webquery_port' => 25575]);
    $server2 = Server::factory()->create(['webquery_port' => 25575]);
    Server::factory()->create(['webquery_port' => null]);

    // excluded
    $request = collect([
        'scope' => 'global',
        'command' => 'say Broadcast',
        'execute_at' => null,
        'servers' => [],
    ]);

    $job = new RunCommandQueuesFromRequestJob($request, 1);
    $job->handle();

    $this->assertDatabaseCount('command_queues', 2);
    $this->assertDatabaseHas('command_queues', ['server_id' => $server1->id]);
    $this->assertDatabaseHas('command_queues', ['server_id' => $server2->id]);
});

test('run command queue job cancels when no webquery port', function () {
    $server = Server::factory()->create(['webquery_port' => null]);
    $commandQueue = CommandQueue::factory()->create(['server_id' => $server->id]);

    $job = new RunCommandQueueJob($commandQueue);
    $job->handle();

    $commandQueue->refresh();
    expect($commandQueue->status)->toEqual(CommandQueueStatus::CANCELLED);
    expect($commandQueue->output)->toEqual('Server does not have webquery port');
});

test('run command queue job skips completed status', function () {
    $commandQueue = CommandQueue::factory()->completed()->create();

    $job = new RunCommandQueueJob($commandQueue);
    $job->handle();

    // Should remain completed, not changed
    $commandQueue->refresh();
    expect($commandQueue->status)->toEqual(CommandQueueStatus::COMPLETED);
});

test('run command queues from request is queued to longtask', function () {
    Queue::fake();

    $request = collect([
        'scope' => 'global',
        'command' => 'say test',
        'execute_at' => null,
        'servers' => [],
    ]);

    RunCommandQueuesFromRequestJob::dispatch($request, 1);

    Queue::assertPushedOn('longtask', RunCommandQueuesFromRequestJob::class);
});

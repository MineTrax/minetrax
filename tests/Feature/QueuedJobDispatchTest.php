<?php

namespace Tests\Feature;

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueuedJobDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_run_command_queues_from_request_creates_queues_for_global_scope()
    {
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
    }

    public function test_run_command_queues_defers_dispatch_when_execute_at_set()
    {
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
    }

    public function test_run_command_queues_uses_all_webquery_servers_when_none_specified()
    {
        Queue::fake([RunCommandQueueJob::class]);

        $server1 = Server::factory()->create(['webquery_port' => 25575]);
        $server2 = Server::factory()->create(['webquery_port' => 25575]);
        Server::factory()->create(['webquery_port' => null]); // excluded

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
    }

    public function test_run_command_queue_job_cancels_when_no_webquery_port()
    {
        $server = Server::factory()->create(['webquery_port' => null]);
        $commandQueue = CommandQueue::factory()->create(['server_id' => $server->id]);

        $job = new RunCommandQueueJob($commandQueue);
        $job->handle();

        $commandQueue->refresh();
        $this->assertEquals(CommandQueueStatus::CANCELLED, $commandQueue->status);
        $this->assertEquals('Server does not have webquery port', $commandQueue->output);
    }

    public function test_run_command_queue_job_skips_completed_status()
    {
        $commandQueue = CommandQueue::factory()->completed()->create();

        $job = new RunCommandQueueJob($commandQueue);
        $job->handle();

        // Should remain completed, not changed
        $commandQueue->refresh();
        $this->assertEquals(CommandQueueStatus::COMPLETED, $commandQueue->status);
    }

    public function test_run_command_queues_from_request_supports_null_user()
    {
        Queue::fake([RunCommandQueueJob::class]);

        $server = Server::factory()->create(['webquery_port' => 25575]);

        $request = collect([
            'scope' => 'global',
            'command' => 'say Hello World',
            'execute_at' => null,
            'servers' => [['id' => $server->id]],
        ]);

        $job = new RunCommandQueuesFromRequestJob($request, null);
        $job->handle();

        $this->assertDatabaseHas('command_queues', [
            'server_id' => $server->id,
            'parsed_command' => 'say Hello World',
            'user_id' => null,
        ]);

        Queue::assertPushed(RunCommandQueueJob::class);
    }

    public function test_run_command_queues_from_request_is_queued_to_longtask()
    {
        Queue::fake();

        $request = collect([
            'scope' => 'global',
            'command' => 'say test',
            'execute_at' => null,
            'servers' => [],
        ]);

        RunCommandQueuesFromRequestJob::dispatch($request, 1);

        Queue::assertPushedOn('longtask', RunCommandQueuesFromRequestJob::class);
    }
}

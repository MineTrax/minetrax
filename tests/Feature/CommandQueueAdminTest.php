<?php

namespace Tests\Feature;

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CommandQueueAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_command_queue_listing()
    {
        $response = $this->get(route('admin.command-queue.index'));
        $response->assertStatus(302);
    }

    public function test_admin_can_view_command_queue_listing()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->get(route('admin.command-queue.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_command_queue_create_page()
    {
        $this->actingAs(User::whereId(1)->first());
        $response = $this->get(route('admin.command-queue.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_store_global_command_queue()
    {
        Queue::fake();

        $admin = User::whereId(1)->first();
        $server = Server::factory()->create(['webquery_port' => 25575]);

        $this->actingAs($admin);
        $response = $this->post(route('admin.command-queue.store'), [
            'scope' => 'global',
            'command' => 'say Hello World',
            'execute_at' => null,
            'servers' => [['id' => $server->id]],
        ]);

        $response->assertRedirect();
        Queue::assertPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_store_validates_scope_is_required()
    {
        $admin = User::whereId(1)->first();
        $this->actingAs($admin);

        $response = $this->post(route('admin.command-queue.store'), [
            'command' => 'say hello',
        ]);

        $response->assertSessionHasErrors(['scope']);
    }

    public function test_store_validates_command_is_required()
    {
        $admin = User::whereId(1)->first();
        $this->actingAs($admin);

        $response = $this->post(route('admin.command-queue.store'), [
            'scope' => 'global',
        ]);

        $response->assertSessionHasErrors(['command']);
    }

    public function test_admin_can_delete_pending_command_queue()
    {
        $admin = User::whereId(1)->first();
        $commandQueue = CommandQueue::factory()->create();

        $this->actingAs($admin);
        $response = $this->delete(route('admin.command-queue.delete'), [
            'id' => $commandQueue->id,
        ]);

        $this->assertDatabaseMissing('command_queues', ['id' => $commandQueue->id]);
    }

    public function test_admin_can_delete_failed_command_queue()
    {
        $admin = User::whereId(1)->first();
        $commandQueue = CommandQueue::factory()->failed()->create();

        $this->actingAs($admin);
        $response = $this->delete(route('admin.command-queue.delete'), [
            'id' => $commandQueue->id,
        ]);

        $this->assertDatabaseMissing('command_queues', ['id' => $commandQueue->id]);
    }

    public function test_admin_can_retry_failed_command_queue()
    {
        Queue::fake();

        $admin = User::whereId(1)->first();
        $commandQueue = CommandQueue::factory()->failed()->create();

        $this->actingAs($admin);
        $response = $this->post(route('admin.command-queue.retry'), [
            'id' => $commandQueue->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals(CommandQueueStatus::PENDING, $commandQueue->fresh()->status);
        Queue::assertPushed(RunCommandQueueJob::class);
    }

    public function test_admin_can_retry_cancelled_command_queue()
    {
        Queue::fake();

        $admin = User::whereId(1)->first();
        $commandQueue = CommandQueue::factory()->cancelled()->create();

        $this->actingAs($admin);
        $response = $this->post(route('admin.command-queue.retry'), [
            'id' => $commandQueue->id,
        ]);

        $response->assertRedirect();
        $this->assertEquals(CommandQueueStatus::PENDING, $commandQueue->fresh()->status);
        Queue::assertPushed(RunCommandQueueJob::class);
    }

    public function test_delete_requires_id()
    {
        $admin = User::whereId(1)->first();
        $this->actingAs($admin);

        $response = $this->delete(route('admin.command-queue.delete'), []);
        $response->assertSessionHasErrors(['id']);
    }

    public function test_non_admin_cannot_access_command_queue_routes()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.command-queue.index'))->assertStatus(302);
        $this->get(route('admin.command-queue.create'))->assertStatus(302);
    }
}

<?php

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueueJob;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('guest cannot access command queue listing', function () {
    $response = $this->get(route('admin.command-queue.index'));
    $response->assertStatus(302);
});

test('admin can view command queue listing', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->get(route('admin.command-queue.index'));
    $response->assertStatus(200);
});

test('admin can view command queue create page', function () {
    $this->actingAs(User::whereId(1)->first());
    $response = $this->get(route('admin.command-queue.create'));
    $response->assertStatus(200);
});

test('admin can store global command queue', function () {
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
});

test('store validates scope is required', function () {
    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->post(route('admin.command-queue.store'), [
        'command' => 'say hello',
    ]);

    $response->assertSessionHasErrors(['scope']);
});

test('store validates command is required', function () {
    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->post(route('admin.command-queue.store'), [
        'scope' => 'global',
    ]);

    $response->assertSessionHasErrors(['command']);
});

test('admin can delete pending command queue', function () {
    $admin = User::whereId(1)->first();
    $commandQueue = CommandQueue::factory()->create();

    $this->actingAs($admin);
    $response = $this->delete(route('admin.command-queue.delete'), [
        'id' => $commandQueue->id,
    ]);

    $this->assertDatabaseMissing('command_queues', ['id' => $commandQueue->id]);
});

test('admin can delete failed command queue', function () {
    $admin = User::whereId(1)->first();
    $commandQueue = CommandQueue::factory()->failed()->create();

    $this->actingAs($admin);
    $response = $this->delete(route('admin.command-queue.delete'), [
        'id' => $commandQueue->id,
    ]);

    $this->assertDatabaseMissing('command_queues', ['id' => $commandQueue->id]);
});

test('admin can retry failed command queue', function () {
    Queue::fake();

    $admin = User::whereId(1)->first();
    $commandQueue = CommandQueue::factory()->failed()->create();

    $this->actingAs($admin);
    $response = $this->post(route('admin.command-queue.retry'), [
        'id' => $commandQueue->id,
    ]);

    $response->assertRedirect();
    expect($commandQueue->fresh()->status)->toEqual(CommandQueueStatus::PENDING);
    Queue::assertPushed(RunCommandQueueJob::class);
});

test('admin can retry cancelled command queue', function () {
    Queue::fake();

    $admin = User::whereId(1)->first();
    $commandQueue = CommandQueue::factory()->cancelled()->create();

    $this->actingAs($admin);
    $response = $this->post(route('admin.command-queue.retry'), [
        'id' => $commandQueue->id,
    ]);

    $response->assertRedirect();
    expect($commandQueue->fresh()->status)->toEqual(CommandQueueStatus::PENDING);
    Queue::assertPushed(RunCommandQueueJob::class);
});

test('delete requires id', function () {
    $admin = User::whereId(1)->first();
    $this->actingAs($admin);

    $response = $this->delete(route('admin.command-queue.delete'), []);
    $response->assertSessionHasErrors(['id']);
});

test('non admin cannot access command queue routes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('admin.command-queue.index'))->assertStatus(302);
    $this->get(route('admin.command-queue.create'))->assertStatus(302);
});

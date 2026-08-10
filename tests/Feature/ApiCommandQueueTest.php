<?php

namespace Tests\Feature;

use App\Enums\CommandQueueStatus;
use App\Jobs\RunCommandQueuesFromRequestJob;
use App\Models\CommandQueue;
use App\Models\Server;
use App\Settings\PluginSettings;
use App\Utils\Helpers\CryptoUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApiCommandQueueTest extends TestCase
{
    use RefreshDatabase;

    private function postJsonWithApiCredentials(string $uri, array $data): TestResponse
    {
        $pluginSettings = app(PluginSettings::class);

        return $this->postJson($uri, $data, [
            'X-API-KEY' => $pluginSettings->plugin_api_key,
            'X-SIGNATURE' => CryptoUtils::generateHmacSignature(json_encode($data), $pluginSettings->plugin_api_secret),
        ]);
    }

    private function getJsonWithApiCredentials(string $uri): TestResponse
    {
        $pluginSettings = app(PluginSettings::class);

        return $this->getJson($uri, [
            'X-API-KEY' => $pluginSettings->plugin_api_key,
            'X-SIGNATURE' => CryptoUtils::generateHmacSignature(url($uri), $pluginSettings->plugin_api_secret),
        ]);
    }

    public function test_request_without_api_credentials_is_rejected()
    {
        $response = $this->postJson('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_key_alone_works_when_signature_validation_disabled()
    {
        config(['minetrax.api_signature_validation' => false]);
        Queue::fake();

        $server = Server::factory()->create(['webquery_port' => 25575]);

        $response = $this->postJson('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
            'servers' => [['id' => $server->id]],
        ], [
            'X-API-KEY' => app(PluginSettings::class)->plugin_api_key,
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['data' => ['request_id']]);

        Queue::assertPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_queues_command_for_player_scope()
    {
        Queue::fake();

        $server = Server::factory()->create(['webquery_port' => 25575]);

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'player',
            'command' => 'lp user Alice parent add booster',
            'execute_at' => null,
            'servers' => [['id' => $server->id]],
            'players' => [
                'scope' => 'all',
                'is_player_online_required' => false,
                'id' => [],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Command queued successfully.',
            ])
            ->assertJsonStructure(['data' => ['request_id']]);

        Queue::assertPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_queues_command_for_global_scope()
    {
        Queue::fake();

        $server = Server::factory()->create(['webquery_port' => 25575]);

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
            'execute_at' => null,
            'servers' => [['id' => $server->id]],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Command queued successfully.',
            ])
            ->assertJsonStructure(['data' => ['request_id']]);

        Queue::assertPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_validates_command_is_required()
    {
        Queue::fake();

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'global',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['command']);

        Queue::assertNotPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_validates_scope_is_required()
    {
        Queue::fake();

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'command' => 'say Hello World',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scope']);

        Queue::assertNotPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_validates_players_is_required_for_player_scope()
    {
        Queue::fake();

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'player',
            'command' => 'lp user Alice parent add booster',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['players']);

        Queue::assertNotPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_validates_server_must_exist()
    {
        Queue::fake();

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
            'servers' => [['id' => 999999]],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['servers.0.id']);

        Queue::assertNotPushed(RunCommandQueuesFromRequestJob::class);
    }

    public function test_it_returns_command_queue_status_by_request_id()
    {
        $requestId = fake()->uuid();
        $pendingCommand = CommandQueue::factory()->create([
            'request_id' => $requestId,
            'status' => CommandQueueStatus::PENDING,
        ]);
        $completedCommand = CommandQueue::factory()->completed()->create([
            'request_id' => $requestId,
        ]);

        $this->getJsonWithApiCredentials("/api/v1/command-queue/{$requestId}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'request_id' => $requestId,
                    'status' => CommandQueueStatus::PENDING->value,
                    'summary' => [
                        CommandQueueStatus::PENDING->value => 1,
                        CommandQueueStatus::COMPLETED->value => 1,
                    ],
                    'commands' => [
                        ['id' => $pendingCommand->id],
                        ['id' => $completedCommand->id],
                    ],
                ],
            ]);
    }

    public function test_it_returns_pending_before_the_request_job_creates_command_queues()
    {
        Queue::fake();

        $server = Server::factory()->create(['webquery_port' => 25575]);

        $response = $this->postJsonWithApiCredentials('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
            'servers' => [['id' => $server->id]],
        ])->assertOk();

        $requestId = $response->json('data.request_id');

        $this->getJsonWithApiCredentials("/api/v1/command-queue/{$requestId}")
            ->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'request_id' => $requestId,
                    'status' => CommandQueueStatus::PENDING->value,
                    'summary' => [],
                    'commands' => [],
                ],
            ]);
    }

    public function test_it_returns_not_found_for_an_unknown_request_id()
    {
        $requestId = fake()->uuid();

        $this->getJsonWithApiCredentials("/api/v1/command-queue/{$requestId}")
            ->assertNotFound()
            ->assertJson([
                'status' => 'error',
                'type' => 'request_not_found',
            ]);
    }
}

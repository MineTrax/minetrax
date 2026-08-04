<?php

namespace Tests\Feature;

use App\Jobs\RunCommandQueuesFromRequestJob;
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

    public function test_request_without_api_credentials_is_rejected()
    {
        $response = $this->postJson('/api/v1/command-queue', [
            'scope' => 'global',
            'command' => 'say Hello World',
        ]);

        $response->assertStatus(401);
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
            ]);

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
            ]);

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
}

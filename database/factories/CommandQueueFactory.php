<?php

namespace Database\Factories;

use App\Enums\CommandQueueStatus;
use App\Models\CommandQueue;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommandQueue>
 */
class CommandQueueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'parsed_command' => 'say hello',
            'status' => CommandQueueStatus::PENDING,
            'max_attempts' => 1,
            'attempts' => 0,
            'tag' => 'run_command',
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommandQueueStatus::FAILED,
            'attempts' => 1,
            'output' => 'Connection refused',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommandQueueStatus::COMPLETED,
            'attempts' => 1,
            'output' => 'OK',
        ]);
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommandQueueStatus::RUNNING,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommandQueueStatus::CANCELLED,
        ]);
    }
}

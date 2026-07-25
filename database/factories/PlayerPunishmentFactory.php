<?php

namespace Database\Factories;

use App\Enums\PlayerPunishmentType;
use App\Models\PlayerPunishment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerPunishment>
 */
class PlayerPunishmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(array_column(PlayerPunishmentType::cases(), 'value')),
            'plugin_name' => $this->faker->randomElement(['litebans', 'advancedban', 'libertybans']),
            'plugin_punishment_id' => $this->faker->uuid,
            'uuid' => $this->faker->uuid,
            'ip_address' => $this->faker->ipv4,
            'player_id' => null,

            'start_at' => $this->faker->dateTime,
            'end_at' => $this->faker->dateTime,

            'reason' => $this->faker->sentence,
            'notes' => $this->faker->paragraph,
            'meta' => null,
            'server_scope' => $this->faker->randomElement(['global', 'local']),
            'origin_server_name' => $this->faker->randomElement(['lobby', 'survival', 'skyblock']),
            'origin_server_id' => null,

            'creator_uuid' => $this->faker->uuid,
            'creator_username' => $this->faker->userName,
        ];
    }
}

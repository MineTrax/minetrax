<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->unique()->uuid(),
            'username' => $this->faker->unique()->userName(),
            'first_seen_at' => now()->subMonths(2),
            'last_seen_at' => now()->subDay(),
        ];
    }

    /**
     * A player that has never been linked to a website account.
     */
    public function unlinked(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_link_after_success_command_run_count' => 0,
        ]);
    }
}

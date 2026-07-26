<?php

namespace Database\Factories;

use App\Models\StoreBan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreBan>
 */
class StoreBanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreBan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'player_uuid' => $this->faker->uuid(),
            'ip_address' => null,
            'email' => null,
            'reason' => $this->faker->sentence(),
            'is_automatic' => false,
            'expires_at' => null,
        ];
    }
}

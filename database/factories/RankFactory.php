<?php

namespace Database\Factories;

use App\Models\Rank;
use Illuminate\Database\Eloquent\Factories\Factory;

class RankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rank::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'shortname' => $this->faker->userName,
            'order' => $this->faker->randomNumber(2),
            'total_score_needed' => $this->faker->randomNumber(2),
            'total_play_time_needed' => $this->faker->randomNumber(2),
        ];
    }
}

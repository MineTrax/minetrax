<?php

namespace Database\Factories;

use App\Models\Shout;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Shout::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'message' => $this->faker->sentence,
            'user_id' => 1
        ];
    }
}

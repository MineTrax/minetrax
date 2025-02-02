<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'shortname' => $this->faker->unique()->slug(2),
            'is_sticky' => $this->faker->boolean,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_by' => User::factory(),
        ];
    }
}
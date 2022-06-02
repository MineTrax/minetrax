<?php

namespace Database\Factories;

use App\Models\CustomPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomPageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomPage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'path' => $this->faker->slug,
            'body' => $this->faker->paragraph,
            'is_in_navbar' => true,
            'is_visible' => true
        ];
    }
}

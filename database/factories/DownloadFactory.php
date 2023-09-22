<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Download>
 */
class DownloadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => $this->faker->name,
            'description' => $this->faker->text,
            'is_external' => $this->faker->boolean,
            'is_exposed_external_url' => $this->faker->boolean,
            'is_only_auth' => $this->faker->boolean,
            'is_active' => $this->faker->boolean,
            'file_name' => $this->faker->userName(),
            'file_size' => $this->faker->randomNumber(),
            'file_path' => $this->faker->filePath(),
            'min_role_weight_required' => $this->faker->randomNumber(),
            'download_count' => $this->faker->randomNumber(),
        ];
    }
}

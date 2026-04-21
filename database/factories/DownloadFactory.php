<?php

namespace Database\Factories;

use App\Models\Download;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Download>
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
        $name = $this->faker->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => $this->faker->text,
            'is_external' => false,
            'is_external_url_hidden' => false,
            'is_only_auth' => false,
            'is_active' => true,
            'file_name' => $this->faker->userName().'.zip',
            'file_url' => null,
            'min_role_weight_required' => null,
            'download_count' => 0,
            'created_by' => 1,
        ];
    }
}

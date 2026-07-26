<?php

namespace Database\Factories;

use App\Models\StorePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorePackage>
 */
class StorePackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StorePackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'store_category_id' => null,
            'name' => $name,
            'slug' => \Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(199, 9999),
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
            'requires_login' => false,
            'is_run_on_all_servers' => false,
            'min_quantity' => 1,
            'max_quantity' => null,
            'sold_count' => 0,
        ];
    }

    /**
     * Set an expiry duration in days.
     */
    public function expiring(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_duration_days' => $days,
        ]);
    }

    /**
     * Mark the package as disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Mark the package as hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }
}

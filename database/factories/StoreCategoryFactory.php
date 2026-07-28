<?php

namespace Database\Factories;

use App\Enums\StoreCategoryDisplayType;
use App\Models\StoreCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreCategory>
 */
class StoreCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => \Str::slug($name),
            'description' => $this->faker->sentence(),
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
            'display_type' => StoreCategoryDisplayType::GRID,
            'comparison_fields' => null,
            'is_cumulative' => false,
        ];
    }

    /**
     * A table comparing the packages in this category.
     *
     * @param  array<int, array{key: string, name: string, description: string|null, type: string}>  $fields
     */
    public function comparison(array $fields = []): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => StoreCategoryDisplayType::COMPARISON,
            'comparison_fields' => $fields ?: [
                ['key' => 'field_1', 'name' => 'Coins', 'description' => 'no of coins', 'type' => 'text'],
                ['key' => 'field_2', 'name' => 'Pro', 'description' => 'is pro?', 'type' => 'check'],
            ],
        ]);
    }

    public function displayedAs(StoreCategoryDisplayType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'display_type' => $type,
        ]);
    }

    /**
     * Owning a cheaper package here credits its price against a dearer one.
     */
    public function cumulative(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cumulative' => true,
        ]);
    }
}

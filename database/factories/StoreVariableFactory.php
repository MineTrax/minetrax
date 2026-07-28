<?php

namespace Database\Factories;

use App\Enums\StoreVariableType;
use App\Models\StoreVariable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreVariable>
 */
class StoreVariableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreVariable::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'identifier' => \Str::slug($name, '_'),
            'description' => $this->faker->sentence(),
            'type' => StoreVariableType::TEXT,
            'options' => null,
            'placeholder' => null,
            'is_required' => true,
            'max_length' => 32,
            'is_enabled' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * A fixed list of choices instead of free text.
     */
    public function select(string $options = 'Red,Green,Blue'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StoreVariableType::SELECT,
            'options' => $options,
            'max_length' => null,
        ]);
    }

    public function radio(string $options = 'Yes,No'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StoreVariableType::RADIO,
            'options' => $options,
            'max_length' => null,
        ]);
    }

    public function number(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StoreVariableType::NUMBER,
            'max_length' => null,
        ]);
    }

    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StoreVariableType::CHECKBOX,
            'is_required' => false,
            'max_length' => null,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => false,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }
}

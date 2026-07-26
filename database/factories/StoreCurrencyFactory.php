<?php

namespace Database\Factories;

use App\Enums\StorePriceRounding;
use App\Models\StoreCurrency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreCurrency>
 */
class StoreCurrencyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreCurrency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->currencyCode(),
            'name' => $this->faker->word(),
            'symbol' => '$',
            'symbol_position' => 'prefix',
            'exponent' => 2,
            'rate_to_base' => 1,
            'is_base' => false,
            'is_enabled' => true,
            'price_rounding' => StorePriceRounding::NONE,
            'sort_order' => 0,
        ];
    }

    /**
     * Mark this currency as the base currency.
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'exponent' => 2,
            'rate_to_base' => 1,
            'is_base' => true,
        ]);
    }

    /**
     * Create a zero-decimal currency (Japanese Yen).
     */
    public function zeroDecimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'JPY',
            'name' => 'Japanese Yen',
            'symbol' => '¥',
            'exponent' => 0,
            'rate_to_base' => 150,
        ]);
    }

    /**
     * Create a three-decimal currency (Kuwaiti Dinar).
     */
    public function threeDecimal(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'KWD',
            'name' => 'Kuwaiti Dinar',
            'symbol' => 'KD',
            'exponent' => 3,
            'rate_to_base' => '0.31',
        ]);
    }
}

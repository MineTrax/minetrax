<?php

namespace Database\Factories;

use App\Enums\StoreDiscountType;
use App\Enums\StoreSaleScope;
use App\Models\StoreSale;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreSaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreSale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'discount_type' => StoreDiscountType::PERCENT,
            'discount_value' => 1000, // basis points, so 10%
            'scope_type' => StoreSaleScope::ALL,
            'min_basket_amount' => null,
            'starts_at' => null,
            'ends_at' => null,
            'is_enabled' => true,
        ];
    }

    /**
     * Scope the sale to named packages. The caller still creates the store_saleables rows.
     */
    public function forPackages(): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => StoreSaleScope::PACKAGES,
        ]);
    }

    /**
     * Scope the sale to named categories. The caller still creates the store_saleables rows.
     */
    public function forCategories(): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => StoreSaleScope::CATEGORIES,
        ]);
    }

    /**
     * Gate the sale on a basket total, in minor units of the base currency.
     */
    public function withMinimum(int $amountMinor): static
    {
        return $this->state(fn (array $attributes) => [
            'min_basket_amount' => $amountMinor,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'ends_at' => now()->subDay(),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->addDay(),
        ]);
    }

    public function fixed(int $amountMinor): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => StoreDiscountType::FIXED,
            'discount_value' => $amountMinor,
        ]);
    }
}

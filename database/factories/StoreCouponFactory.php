<?php

namespace Database\Factories;

use App\Enums\StoreDiscountType;
use App\Models\StoreCoupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreCoupon>
 */
class StoreCouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreCoupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('SAVE##??')),
            'description' => null,
            'discount_type' => StoreDiscountType::PERCENT,
            'discount_value' => 1000, // basis points, so 10%
            'currency_code' => null,
            'min_basket_amount' => null,
            'max_uses_total' => null,
            'max_uses_per_user' => null,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_enabled' => true,
        ];
    }

    /**
     * A fixed-amount coupon in minor units of the given currency.
     */
    public function fixed(int $amountMinor, ?string $currencyCode = null): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => StoreDiscountType::FIXED,
            'discount_value' => $amountMinor,
            'currency_code' => $currencyCode,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }
}

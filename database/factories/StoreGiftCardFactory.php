<?php

namespace Database\Factories;

use App\Models\StoreCurrency;
use App\Models\StoreGiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreGiftCard>
 */
class StoreGiftCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreGiftCard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Same shape the service mints, so a factory card is typeable in the cart like a real one.
            'code' => strtoupper($this->faker->unique()->bothify('??##-??##-??##')),
            // Cards are denominated in a real store currency, so this follows the base rather than
            // inventing a code the currency service could not find.
            'currency_code' => StoreCurrency::firstWhere('is_base', true)?->code ?? 'USD',
            'original_balance' => 2000,
            'balance' => 2000,
            'expires_at' => null,
            'is_enabled' => true,
            'issued_to_user_id' => null,
        ];
    }

    /**
     * A card with part of its balance already spent.
     */
    public function partlySpent(int $remainingMinor): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $remainingMinor,
        ]);
    }

    public function emptied(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
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

<?php

namespace Database\Factories;

use App\Models\StoreReferral;
use App\Models\StoreReferralPayout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreReferralPayout>
 */
class StoreReferralPayoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreReferralPayout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_referral_id' => StoreReferral::factory(),
            'amount' => 500, // minor units
            'currency' => 'USD',
            'reference' => null,
            'note' => null,
            'paid_at' => now(),
        ];
    }

    public function of(int $amountMinor): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amountMinor,
        ]);
    }
}

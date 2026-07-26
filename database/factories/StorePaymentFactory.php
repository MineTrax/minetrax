<?php

namespace Database\Factories;

use App\Enums\StorePaymentGateway;
use App\Enums\StorePaymentStatus;
use App\Models\StoreOrder;
use App\Models\StorePayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorePayment>
 */
class StorePaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StorePayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_order_id' => StoreOrder::factory(),
            'gateway' => StorePaymentGateway::MANUAL,
            'status' => StorePaymentStatus::PENDING,
            'amount' => $this->faker->numberBetween(199, 9999),
            'currency' => 'USD',
            'refunded_amount' => 0,
        ];
    }

    /**
     * Mark the payment as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StorePaymentStatus::COMPLETED,
            'paid_at' => now(),
        ]);
    }
}

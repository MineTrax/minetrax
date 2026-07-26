<?php

namespace Database\Factories;

use App\Enums\StoreDeliveryStatus;
use App\Enums\StoreOrderStatus;
use App\Models\StoreOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreOrder>
 */
class StoreOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(199, 9999);

        return [
            'player_uuid' => $this->faker->uuid(),
            'player_username' => $this->faker->userName(),
            'currency' => 'USD',
            'base_currency' => 'USD',
            'exchange_rate' => 1,
            'subtotal' => $amount,
            'total' => $amount,
            'amount_due' => $amount,
            'base_total' => $amount,
            'sale_discount' => 0,
            'coupon_discount' => 0,
            'tax_amount' => 0,
            'gift_card_amount' => 0,
            'status' => StoreOrderStatus::PENDING,
            'delivery_status' => StoreDeliveryStatus::PENDING,
            'email' => $this->faker->safeEmail(),
            'user_id' => null,
            'ip_address' => $this->faker->ipv4(),
        ];
    }

    /**
     * Mark the order as paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreOrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark the order as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StoreOrderStatus::COMPLETED,
            'paid_at' => now(),
            'completed_at' => now(),
            'delivery_status' => StoreDeliveryStatus::DELIVERED,
        ]);
    }

    /**
     * Mark the order as a guest order.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'email' => $this->faker->safeEmail(),
        ]);
    }

    /**
     * Associate the order with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Enums\StoreReferralAttributionMode;
use App\Models\StoreCoupon;
use App\Models\StoreReferral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreReferral>
 */
class StoreReferralFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreReferral::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('CREATOR##??')),
            'referrer_name' => $this->faker->userName(),
            'user_id' => null,
            'share_bp' => 500, // basis points, so 5%
            'store_coupon_id' => null,
            'is_url_tracking_enabled' => true,
            'attribution_window_days' => 3,
            'attribution_mode' => StoreReferralAttributionMode::FIRST_TOUCH,
            'is_command_execution_enabled' => false,
            'visit_count' => 0,
            'last_visited_at' => null,
            'is_enabled' => true,
            'notes' => null,
        ];
    }

    /**
     * Tied to a member, which is what unlocks their own stats page — and what stops the code
     * earning them a commission on their own purchases.
     */
    public function forUser(User|int $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }

    public function withCoupon(StoreCoupon|int $coupon): static
    {
        return $this->state(fn (array $attributes) => [
            'store_coupon_id' => $coupon instanceof StoreCoupon ? $coupon->id : $coupon,
        ]);
    }

    public function share(int $basisPoints): static
    {
        return $this->state(fn (array $attributes) => [
            'share_bp' => $basisPoints,
        ]);
    }

    public function mode(StoreReferralAttributionMode $mode): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_mode' => $mode,
        ]);
    }

    /**
     * No attribution window at all, so the cookie lasts as long as the browser keeps it.
     */
    public function lifetime(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_window_days' => null,
        ]);
    }

    public function withCommands(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_command_execution_enabled' => true,
        ]);
    }

    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    public function withoutUrlTracking(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_url_tracking_enabled' => false,
        ]);
    }
}

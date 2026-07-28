<?php

namespace Database\Factories;

use App\Enums\StorePackageRequirementMode;
use App\Enums\StorePackageType;
use App\Models\StorePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorePackage>
 */
class StorePackageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StorePackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'store_category_id' => null,
            'name' => $name,
            'slug' => \Str::slug($name),
            'short_description' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => StorePackageType::MINECRAFT_PACKAGE,
            'price' => $this->faker->numberBetween(199, 9999),
            'discount_bp' => 0,
            'is_pay_what_you_want' => false,
            'sort_order' => 0,
            'is_visible' => true,
            'is_enabled' => true,
            'requires_login' => false,
            'is_featured' => false,
            'is_giftable' => false,
            'min_quantity' => 1,
            'max_quantity' => null,
            'sold_count' => 0,
            'required_packages_mode' => StorePackageRequirementMode::ALL,
        ];
    }

    /**
     * Set an expiry duration in days.
     */
    public function expiring(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_duration_days' => $days,
        ]);
    }

    /**
     * Mark the package as disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
        ]);
    }

    /**
     * Mark the package as hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Let the buyer name their own price, with `price` as the floor.
     */
    public function payWhatYouWant(?int $maximum = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pay_what_you_want' => true,
            'pay_what_you_want_max' => $maximum,
        ]);
    }

    /**
     * A percentage off, in basis points: 2000 is 20%.
     */
    public function discounted(int $basisPoints = 2000): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_bp' => $basisPoints,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function giftable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_giftable' => true,
        ]);
    }

    /**
     * Sells store credit rather than an in-game delivery.
     */
    public function giftCard(?int $amount = 5000, bool $sameAsPrice = false): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StorePackageType::GIFTCARD,
            'gift_card_amount' => $sameAsPrice ? null : $amount,
            'is_gift_card_amount_same_as_price' => $sameAsPrice,
        ]);
    }

    /**
     * Both an in-game delivery and store credit from the one purchase.
     */
    public function packageAndGiftCard(?int $amount = 5000): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => StorePackageType::BOTH,
            'gift_card_amount' => $amount,
        ]);
    }

    /**
     * A publish window. Both ends are optional, so this covers "not yet live" and "expired".
     */
    public function window(?string $from = null, ?string $until = null): static
    {
        return $this->state(fn (array $attributes) => [
            'available_from' => $from,
            'available_until' => $until,
        ]);
    }
}

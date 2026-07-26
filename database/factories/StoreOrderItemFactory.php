<?php

namespace Database\Factories;

use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
use App\Models\StorePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreOrderItem>
 */
class StoreOrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreOrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(199, 9999);

        return [
            'store_order_id' => StoreOrder::factory(),
            'store_package_id' => StorePackage::factory(),
            'package_name' => $this->faker->words(3, true),
            'quantity' => 1,
            'unit_price_original' => $price,
            'unit_price' => $price,
            'total' => $price,
            'options' => null,
        ];
    }
}

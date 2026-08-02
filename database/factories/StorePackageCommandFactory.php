<?php

namespace Database\Factories;

use App\Enums\StorePackageCommandTrigger;
use App\Models\StorePackage;
use App\Models\StorePackageCommand;
use App\Models\StoreSale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorePackageCommand>
 */
class StorePackageCommandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StorePackageCommand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_package_id' => StorePackage::factory(),
            'store_sale_id' => null,
            'trigger' => StorePackageCommandTrigger::PURCHASE,
            'command' => 'give {PLAYER_USERNAME} diamond 1',
            'is_player_online_required' => false,
            'delay_seconds' => 0,
            'is_run_on_all_servers' => true,
            'is_run_on_all_packages' => true,
            'is_repeat_per_quantity' => false,
            'sort_order' => 0,
        ];
    }

    /**
     * A command owned by a sale rather than a package. Exactly one owner is allowed, so this clears
     * the package the default state would otherwise create.
     */
    public function forSale(StoreSale|int $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'store_package_id' => null,
            'store_sale_id' => $sale instanceof StoreSale ? $sale->id : $sale,
        ]);
    }

    /**
     * Set trigger to expiry.
     */
    public function expiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StorePackageCommandTrigger::EXPIRY,
        ]);
    }

    /**
     * Set trigger to refund.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StorePackageCommandTrigger::REFUND,
        ]);
    }

    /**
     * Set trigger to chargeback.
     */
    public function chargeback(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StorePackageCommandTrigger::CHARGEBACK,
        ]);
    }

    /**
     * Set target to all servers.
     */
    public function allServers(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_run_on_all_servers' => true,
        ]);
    }
}

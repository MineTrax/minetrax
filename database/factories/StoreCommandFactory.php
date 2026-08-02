<?php

namespace Database\Factories;

use App\Enums\StoreCommandTrigger;
use App\Models\StoreCommand;
use App\Models\StorePackage;
use App\Models\StoreSale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<StoreCommand>
 */
class StoreCommandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StoreCommand::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'commandable_type' => StorePackage::class,
            'commandable_id' => StorePackage::factory(),
            'trigger' => StoreCommandTrigger::PURCHASE,
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
     * A command owned by anything registered in config('store.command_owners').
     *
     * Clears the package the default state would otherwise create — a morph holds one owner, and
     * leaving the default in place would build a package nothing points at.
     */
    public function forOwner(Model $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'commandable_type' => $owner->getMorphClass(),
            'commandable_id' => $owner->getKey(),
        ]);
    }

    /**
     * A command owned by a sale rather than a package.
     */
    public function forSale(StoreSale|int $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'commandable_type' => StoreSale::class,
            'commandable_id' => $sale instanceof StoreSale ? $sale->id : $sale,
        ]);
    }

    /**
     * Set trigger to expiry.
     */
    public function expiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StoreCommandTrigger::EXPIRY,
        ]);
    }

    /**
     * Set trigger to refund.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StoreCommandTrigger::REFUND,
        ]);
    }

    /**
     * Set trigger to chargeback.
     */
    public function chargeback(): static
    {
        return $this->state(fn (array $attributes) => [
            'trigger' => StoreCommandTrigger::CHARGEBACK,
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

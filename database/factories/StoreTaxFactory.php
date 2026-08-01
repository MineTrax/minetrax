<?php

namespace Database\Factories;

use App\Models\StoreTax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreTax>
 */
class StoreTaxFactory extends Factory
{
    protected $model = StoreTax::class;

    public function definition(): array
    {
        return [
            'name' => 'VAT',
            // The fallback rule by default: country_id is unique, so a factory that picked one at
            // random would collide the moment a test made two.
            'country_id' => null,
            'rate_bp' => 2000,
            'is_inclusive' => false,
            'is_enabled' => true,
        ];
    }

    public function inclusive(): static
    {
        return $this->state(fn () => ['is_inclusive' => true]);
    }

    public function forCountry(int $countryId): static
    {
        return $this->state(fn () => ['country_id' => $countryId]);
    }
}

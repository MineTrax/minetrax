<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ip_address' => $this->faker->ipv4,
            'join_port' => $this->faker->numberBetween(0, 25565),
            'query_port' => $this->faker->numberBetween(0, 25565),
            'hostname' => $this->faker->domainName,
            'minecraft_version' => $this->faker->randomElement(['1.16', '1.15', '1.14']),
            'name' => $this->faker->company,
            'description' => $this->faker->paragraph,
            'storage_login' => 'eyJpdiI6Ikx1M3VFSkhVNTFIM3VKbkFGTjhJTGc9PSIsInZhbHVlIjoiUFhBVklqdjlaWWZkNXRYOS9DbndmVU5uejJPR3NYbWpqajRmOWVxKzN4cnhKSDJrN2Q2UHBvOEordmMySnArZ3ZjbGxNemtwOTJlUTJ2ZzZlTDZaRUFOZzhseXRFcnVlTXdCTkNWOWpqK2U5azJsamFWaTVWQ0FNSXN3MzRDZFhYMEplenZHSFdwMWplZnhYVDFCZitvZGsydmp2dU1ZbVNaSTIxT2tyL2R3PSIsIm1hYyI6IjNmMzdiMGQwZDQ4NjE1NzMwZmI5NTA4MjMyOWUyNTJhZWQ0MTY4MGNkMTExMGNiOWRiYzRjZWJmNTE0MGVlNGEifQ',
            'country_id' => $this->faker->randomNumber(2),
            'level_name' => 'world'
        ];
    }
}

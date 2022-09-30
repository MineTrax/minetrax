<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ReactionTypeInitSeeder::class,
            CountrySeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            RankSeeder::class,
            CustomPageSeeder::class
        ]);
        // \App\Models\User::factory(10)->create();
    }
}

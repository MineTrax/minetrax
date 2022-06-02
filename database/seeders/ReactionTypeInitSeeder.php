<?php

namespace Database\Seeders;

use Artisan;
use Illuminate\Database\Seeder;

class ReactionTypeInitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('love:reaction-type-add --default');
    }
}

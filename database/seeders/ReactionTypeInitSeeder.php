<?php

namespace Database\Seeders;

use Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReactionTypeInitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reactionsInDb = DB::table('love_reaction_types')->exists();
        if ($reactionsInDb) {
            return;
        }

        Artisan::call('love:reaction-type-add --default');
    }
}

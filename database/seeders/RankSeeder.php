<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $ranksInDb = DB::table('ranks')->exists();
        if ($ranksInDb) {
            return;
        }

        $data = json_decode(file_get_contents(storage_path('seed')."/ranks.json"), true);
        \DB::table('ranks')->insert($data);
    }
}

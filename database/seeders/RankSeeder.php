<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents(storage_path('seed')."/ranks.json"), true);
        \DB::table('ranks')->insert($data);
    }
}

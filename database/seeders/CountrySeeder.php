<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Only run if this seeder hasn't run ie when countries table is empty.
        $countriesInDb = DB::table('countries')->exists();
        if ($countriesInDb) {
            return;
        }

        $data = json_decode(file_get_contents(storage_path('seed')."/countries.json"), true);
        $id = 1;
        foreach ($data as $country) {
            $c['id'] = $id;
            $c['name'] = $country['name']['common'];
            $c['iso_code'] = $country['cca2'];
            $c['region'] = $country['region'];
            $c['currency'] = array_values($country['currencies']) ? json_encode(array_values($country['currencies'])[0]) : null;
            $c['languages'] = $country['languages'] ? json_encode($country['languages']) : null;
            $c['flag'] = $country['flag'];
            $c['is_un_member'] = $country['unMember'];
            $c['is_independent'] = $country['independent'];
            $c['status'] = $country['status'];
            $c['latlng'] = json_encode($country['latlng']);
            $c['created_at'] = now();
            $c['updated_at'] = now();
            DB::table('countries')->insert($c);
            $id++;
        }
    }
}

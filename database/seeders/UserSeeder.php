<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $rolesInDb = DB::table('users')->exists();
        if ($rolesInDb) {
            return;
        }

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'username' => 'superadmin',
            'email_verified_at' => now(),
            'user_setup_status' => 1,
            'verified_at' => now(),
            'password' => \Hash::make('admin123'),
            'country_id' => Country::UNKNOWN_COUNTRY_ID
        ]);

        $admin->assignRole(Role::SUPER_ADMIN_ROLE_NAME);
    }
}

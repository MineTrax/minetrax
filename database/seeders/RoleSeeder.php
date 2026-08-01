<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rolesInDb = DB::table('roles')->exists();
        if ($rolesInDb) {
            return;
        }

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::create(['name' => 'superadmin', 'display_name' => 'Super Admin', 'is_staff' => true, 'weight' => 99, 'web_message_format' => '&a&l{USERNAME}&r&f']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin', 'display_name' => 'Admin', 'is_staff' => true, 'weight' => 3]);
        $admin->givePermissionTo(['create news', 'read news', 'update news', 'delete news',
            'create polls', 'read polls', 'update polls', 'delete polls',
            'read users', 'update users', 'ban users', 'mute users', 'warn users',
            'delete shouts', 'delete posts', 'delete comments', 'kick players', 'kill players',
            'mute players', 'send server_broadcasts', 'read custom_form_submissions', 'delete custom_form_submissions',
            'create store_categories', 'read store_categories', 'update store_categories', 'delete store_categories',
            'create store_packages', 'read store_packages', 'update store_packages', 'delete store_packages',
            'read store_currencies', 'update store_currencies',
            'read store_taxes',
            'read store_orders', 'update store_orders', 'refund store_orders', 'resend store_orders',
            'read store_payments', 'view store_statistics',
            'create store_coupons', 'read store_coupons', 'update store_coupons', 'delete store_coupons',
            'create store_sales', 'read store_sales', 'update store_sales', 'delete store_sales',
            'create store_bans', 'read store_bans', 'update store_bans', 'delete store_bans']);

        $moderator = Role::create(['name' => 'moderator', 'display_name' => 'Moderator', 'is_staff' => true, 'weight' => 2]);
        $moderator->givePermissionTo(['read users', 'mute users', 'warn users', 'delete shouts', 'delete comments', 'kill players', 'mute players']);

        $user = Role::create(['name' => 'default', 'display_name' => 'User', 'weight' => 1]);
    }
}

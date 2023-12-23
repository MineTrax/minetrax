<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::create(['name' => 'superadmin', 'display_name' => 'Super Admin', 'is_staff' => true, 'weight' => 99, 'web_message_format' => '&a&l{USERNAME}&r&f']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin', 'display_name' => 'Admin', 'is_staff' => true, 'weight' => 3]);
        $admin->givePermissionTo(['create news', 'read news', 'update news', 'delete news',
            'create polls', 'read polls', 'update polls', 'delete polls',
            'read users', 'update users', 'ban users', 'mute users', 'warn users',
            'delete shouts', 'delete posts', 'delete comments', 'kick players', 'kill players',
            'mute players', 'send server_broadcasts', 'read custom_form_submissions', 'delete custom_form_submissions']);

        $moderator = Role::create(['name' => 'moderator', 'display_name' => 'Moderator', 'is_staff' => true, 'weight' => 2]);
        $moderator->givePermissionTo(['read users', 'mute users', 'warn users', 'delete shouts', 'delete comments', 'kill players', 'mute players']);

        $user = Role::create(['name' => 'default', 'display_name' => 'User', 'weight' => 1]);
    }
}

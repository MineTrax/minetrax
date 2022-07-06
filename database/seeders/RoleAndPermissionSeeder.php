<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
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

        Permission::create(['name' => 'create servers']);
        Permission::create(['name' => 'read servers']);
        Permission::create(['name' => 'update servers']);
        Permission::create(['name' => 'delete servers']);

        Permission::create(['name' => 'create roles']);
        Permission::create(['name' => 'read roles']);
        Permission::create(['name' => 'update roles']);
        Permission::create(['name' => 'delete roles']);
        Permission::create(['name' => 'assign roles']); // Let user to assign roles which weight less than his own to other users

        Permission::create(['name' => 'create ranks']);
        Permission::create(['name' => 'read ranks']);
        Permission::create(['name' => 'update ranks']);
        Permission::create(['name' => 'delete ranks']);

        Permission::create(['name' => 'read users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'ban users']);
        Permission::create(['name' => 'mute users']);
        Permission::create(['name' => 'warn users']);

        Permission::create(['name' => 'create news']);
        Permission::create(['name' => 'read news']);
        Permission::create(['name' => 'update news']);
        Permission::create(['name' => 'delete news']);

        Permission::create(['name' => 'create polls']);
        Permission::create(['name' => 'read polls']);
        Permission::create(['name' => 'update polls']);
        Permission::create(['name' => 'delete polls']);

        Permission::create(['name' => 'create custom_pages']);
        Permission::create(['name' => 'read custom_pages']);
        Permission::create(['name' => 'update custom_pages']);
        Permission::create(['name' => 'delete custom_pages']);

        Permission::create(['name' => 'read sessions']);
        Permission::create(['name' => 'delete sessions']);

        Permission::create(['name' => 'delete shouts']);
        Permission::create(['name' => 'delete posts']);
        Permission::create(['name' => 'delete comments']);

        Permission::create(['name' => 'update settings']);

        Permission::create(['name' => 'view server_consolelogs']);

        // Player & Server Administration
        Permission::create(['name' => 'kick players']);
        Permission::create(['name' => 'ban players']);
        Permission::create(['name' => 'kill players']);
        Permission::create(['name' => 'mute players']);
        Permission::create(['name' => 'send server_custom_commands']);
        Permission::create(['name' => 'send server_broadcasts']);

        $superAdmin = Role::create(['name' => 'superadmin', 'display_name' => 'Super Admin', 'is_staff' => true, 'weight' => 99, 'web_message_format' => '&a&l{USERNAME}&r&f']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::create(['name' => 'admin', 'display_name' => 'Admin', 'is_staff' => true, 'weight' => 3]);
        $admin->givePermissionTo(['create news', 'read news', 'update news', 'delete news',
                'create polls', 'read polls', 'update polls', 'delete polls',
                'read users', 'update users', 'ban users', 'mute users', 'warn users', 'delete shouts', 'delete posts', 'delete comments', 'kick players', 'kill players', 'mute players', 'send server_broadcasts']);

        $moderator = Role::create(['name' => 'moderator', 'display_name' => 'Moderator', 'is_staff' => true, 'weight' => 2]);
        $moderator->givePermissionTo(['read users', 'mute users', 'warn users', 'delete shouts', 'delete comments', 'kill players', 'mute players']);

        $user = Role::create(['name' => 'default', 'display_name' => 'User', 'weight' => 1]);
    }
}

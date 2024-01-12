<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::findOrCreate('create servers');
        Permission::findOrCreate('read servers');
        Permission::findOrCreate('update servers');
        Permission::findOrCreate('delete servers');

        Permission::findOrCreate('create roles');
        Permission::findOrCreate('read roles');
        Permission::findOrCreate('update roles');
        Permission::findOrCreate('delete roles');
        Permission::findOrCreate('assign roles'); // Let user to assign roles which weight less than his own to other users

        Permission::findOrCreate('create ranks');
        Permission::findOrCreate('read ranks');
        Permission::findOrCreate('update ranks');
        Permission::findOrCreate('delete ranks');

        Permission::findOrCreate('read users');
        Permission::findOrCreate('update users');
        Permission::findOrCreate('delete users');
        Permission::findOrCreate('ban users');
        Permission::findOrCreate('impersonate users');
        Permission::findOrCreate('mute users');
        Permission::findOrCreate('warn users');

        Permission::findOrCreate('create news');
        Permission::findOrCreate('read news');
        Permission::findOrCreate('update news');
        Permission::findOrCreate('delete news');

        Permission::findOrCreate('create polls');
        Permission::findOrCreate('read polls');
        Permission::findOrCreate('update polls');
        Permission::findOrCreate('delete polls');

        Permission::findOrCreate('create custom_pages');
        Permission::findOrCreate('read custom_pages');
        Permission::findOrCreate('update custom_pages');
        Permission::findOrCreate('delete custom_pages');

        Permission::findOrCreate('read sessions');
        Permission::findOrCreate('delete sessions');

        Permission::findOrCreate('delete shouts');
        Permission::findOrCreate('delete posts');
        Permission::findOrCreate('delete comments');

        Permission::findOrCreate('update settings');

        Permission::findOrCreate('view server_consolelogs');

        // Player & Server Administration
        Permission::findOrCreate('kick players');
        Permission::findOrCreate('ban players');
        Permission::findOrCreate('kill players');
        Permission::findOrCreate('mute players');
        Permission::findOrCreate('send server_custom_commands');
        Permission::findOrCreate('send server_broadcasts');

        Permission::findOrCreate('create badges');
        Permission::findOrCreate('read badges');
        Permission::findOrCreate('update badges');
        Permission::findOrCreate('delete badges');
        Permission::findOrCreate('assign badges');
        Permission::findOrCreate('lock badges'); // Locked badges can't be asssigned to anyone or removed. they became kinda special limited offer badge.

        Permission::findOrCreate('view admin_dashboard');
        Permission::findOrCreate('view server_intel');
        Permission::findOrCreate('view player_intel_critical'); // View critical player_intel which only admin should view, player ip, inventory data etc.

        Permission::findOrCreate('use ask_db');

        Permission::findOrCreate('create downloads');
        Permission::findOrCreate('read downloads');
        Permission::findOrCreate('update downloads');
        Permission::findOrCreate('delete downloads');

        Permission::findOrCreate('create custom_forms');
        Permission::findOrCreate('read custom_forms');
        Permission::findOrCreate('update custom_forms');
        Permission::findOrCreate('delete custom_forms');
        Permission::findOrCreate('read custom_form_submissions');
        Permission::findOrCreate('delete custom_form_submissions');

        Permission::findOrCreate('view pulse_admin_dashboard');
    }
}

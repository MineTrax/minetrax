<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $users = DB::table('users')->cursor();
        foreach ($users as $user) {
            if (! $user->provider_name) {
                continue;
            }

            DB::table('social_accounts')->insertOrIgnore([
                'user_id' => $user->id,
                'provider' => $user->provider_name,
                'provider_id' => $user->provider_id,
                'email' => $user->email,
                'nickname' => $user->username,
                'name' => $user->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $socialAccounts = DB::table('social_accounts')->cursor();

        foreach ($socialAccounts as $account) {
            DB::table('users')->where('id', $account->user_id)->update([
                'provider_name' => $account->provider,
                'provider_id' => $account->provider_id,
            ]);
        }
    }
};

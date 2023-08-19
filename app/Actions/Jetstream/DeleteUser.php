<?php

namespace App\Actions\Jetstream;

use DB;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        // Cannot delete the first user. -> (superadmin)
        if ($user->id === 1) {
            abort(403, 'Cannot delete the first user.');
        }

        // Start Transaction
        DB::beginTransaction();
        try {
            // Delete posts and its media
            $user->posts->each->delete();

            // Delete assigned badges
            $user->badges()->detach();

            // Delete permissions and roles
            $user->syncRoles([]);
            $user->syncPermissions([]);

            // Delete notifications
            $user->notifications()->delete();

            // Delete the user
            $user->deleteProfilePhoto();
            $user->tokens->each->delete();
            $user->delete();

            // End Transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            throw $e;
        }
    }
}

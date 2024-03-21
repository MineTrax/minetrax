<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;

class ConfirmPassword
{
    /**
     * Confirm that the given password is valid for the given user.
     *
     * @param  mixed  $user
     * @return bool
     */
    public function __invoke(User $user, ?string $password = null)
    {
        if ($user->password == null) {
            return true;
        }

        return Hash::check($password, $user->password);
    }
}

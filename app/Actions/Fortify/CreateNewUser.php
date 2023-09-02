<?php

namespace App\Actions\Fortify;

use App\Models\Role;
use App\Models\User;
use App\Services\GeolocationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(protected GeolocationService $geolocationService)
    {
        // Hm...
    }

    /**
     * Validate and create a newly registered user.
     *
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        $countryId = $this->geolocationService->getCountryIdFromIP(request()->ip());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->uncompromised()],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'username' => $input['username'],
            'password' => Hash::make($input['password']),
            'country_id' => $countryId,
            'user_setup_status' => 1,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
        $user->assignRole(Role::DEFAULT_ROLE_NAME);

        return $user;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\GeolocationService;
use App\Utils\Helpers\Helper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function __construct(protected GeolocationService $geolocationService) {}

    public function redirect($provider, Request $request)
    {
        if ($request->wantsJson()) {
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
            return response()->json([
                'redirect_url' => $redirectUrl
            ]);
        }
        return Socialite::driver($provider)->redirect();
    }

    public function handleCallback($provider, Request $request)
    {
        $socialUser = null;
        if ($request->wantsJson()) {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } else {
            $socialUser = Socialite::driver($provider)->user();
        }

        // If there is no email or name address then error
        if (!$socialUser->getEmail() && !$socialUser->getName()) {
            return redirect()->route('login')
                ->with(['toast' => ['type' => 'danger', 'title' => 'Unable to fetch your email address or name.', 'milliseconds' => 7000]]);
        }

        // Check if user already exists
        $user = User::where('email', $socialUser->getEmail())->first();

        // If user exists and has different provider then return error about it
        if ($user && ($socialUser->getId() != $user->provider_id || $provider != $user->provider_name)) {
            $providerHelper = $user->provider_name ? ucfirst($user->provider_name) : " with password";
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Provider mismatch. You used a different provider while registration. Maybe try '. $providerHelper.'?'], 422);
            }
            return redirect()->route('login')
            ->with(['toast' => ['type' => 'danger', 'title' => 'You used a different provider during registration. Maybe try '. $providerHelper.'?', 'milliseconds' => 7000]]);
        }

        // else generate and token if wants json else login with session
        if ($user) {
            if ($request->wantsJson()) {
                // Generate token
                $token = $user->createToken('default');
                return response()->json([
                    'token_type'      => 'Bearer',
                    'access_token' => $token->plainTextToken,
                ]);
            } else {
                Auth::login($user);
                return redirect()->home();
            }
        }

        $countryId = $this->geolocationService->getCountryIdFromIP(request()->ip());
        // Create user if not exists
        $data = [
            'username' => Helper::uniqidReal(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'provider_name' => $provider,
            'provider_id' => $socialUser->getId(),
            'password' => null,
            'country_id' => $countryId,
            'email_verified_at' => now()
        ];
        $user = User::create($data);
        $user->assignRole(Role::DEFAULT_ROLE_NAME);
        event(new Registered($user));
        // Return created user with token if wantsJson else login and redirect
        if ($request->wantsJson()) {
            $token = $user->createToken('default');
            return response()->json([
                'message'      => 'Registration Successful',
                'data'         => $user,
                'access_token' => $token->plainTextToken,
            ]);
        } else {
            Auth::login($user);
            return redirect()->home();
        }
    }
}

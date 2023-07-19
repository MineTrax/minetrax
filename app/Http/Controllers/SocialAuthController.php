<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\GeolocationService;
use App\Utils\Helpers\Helper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Route;

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
                ->with(['toast' => ['type' => 'danger', 'title' => __('Unable to fetch your email address or name.'), 'milliseconds' => 7000]]);
        }

        // Check if user already exists
        $user = User::where('email', $socialUser->getEmail())->first();


        $allowAnyProviderAuth = config('auth.any_provider_social_auth');
        // If user exists and has different provider then return error about it
        if ($user && !$allowAnyProviderAuth && ($socialUser->getId() != $user->provider_id || $provider != $user->provider_name)) {
            $providerHelper = $user->provider_name ? ucfirst($user->provider_name) : " with password";
            if ($request->wantsJson()) {
                return response()->json(['message' => __('Provider mismatch. You used a different provider while registration. Maybe try :provider?', ['provider' => $providerHelper])], 422);
            }
            return redirect()->route('login')
            ->with(['toast' => ['type' => 'danger', 'title' => __('Provider mismatch. You used a different provider while registration. Maybe try :provider?', ['provider' => $providerHelper]), 'milliseconds' => 7000]]);
        }

        // else generate and token if wants json else login with session
        if ($user) {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            if ($request->wantsJson()) {
                // Generate token
                $token = $user->createToken('default');
                return response()->json([
                    'token_type'      => 'Bearer',
                    'access_token' => $token->plainTextToken,
                ]);
            } else {
                Auth::login($user);
                return redirect()->route('home');
            }
        }

        // Before creating new user check if registration feature is disabled.
        if (!Route::has("register"))
        {
            return redirect()->route('login')
            ->with(['toast' => ['type' => 'danger', 'title' => __('New user registration is disabled!'), 'milliseconds' => 5000]]);
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
            'email_verified_at' => now(),
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ];
        $user = User::create($data);
        $user->assignRole(Role::DEFAULT_ROLE_NAME);
        event(new Registered($user));
        // Return created user with token if wantsJson else login and redirect
        if ($request->wantsJson()) {
            $token = $user->createToken('default');
            return response()->json([
                'message'      => __('Registration Successful'),
                'data'         => $user,
                'access_token' => $token->plainTextToken,
            ]);
        } else {
            Auth::login($user);
            return redirect()->route('home');
        }
    }
}

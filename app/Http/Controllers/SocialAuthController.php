<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\GeolocationService;
use App\Settings\GeneralSettings;
use App\Utils\Helpers\Helper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Socialite\Facades\Socialite;
use NotificationChannels\Discord\Discord;
use GuzzleHttp\Client;
use Log;
use Route;

class SocialAuthController extends Controller
{
    public function __construct(protected GeolocationService $geolocationService)
    {
    }

    public function redirect($provider, Request $request)
    {
        $forceJoinDiscordServer = config('services.discord.force_join_server');

        if ($provider === 'twitter') {
            $provider = 'twitter-oauth-2';
        }

        if ($request->wantsJson()) {
            $socialiteDriver = Socialite::driver($provider)->stateless();
            if ($provider === 'discord' && $forceJoinDiscordServer) {
                $socialiteDriver->scopes(['guilds.join']);
            }
            $redirectUrl = $socialiteDriver->redirect()->getTargetUrl();

            return response()->json([
                'redirect_url' => $redirectUrl,
            ]);
        }

        $socialiteDriver = Socialite::driver($provider);
        if ($provider === 'discord' && $forceJoinDiscordServer) {
            $socialiteDriver->scopes(['guilds.join']);
        }
        return $socialiteDriver->redirect();
    }

    public function handleCallback($provider, Request $request)
    {
        if ($provider === 'twitter') {
            $provider = 'twitter-oauth-2';
        }
        $allowAnyProviderAuth = config('auth.any_provider_social_auth');
        $socialUser = null;
        try {
            if ($request->wantsJson()) {
                $socialUser = Socialite::driver($provider)->stateless()->user();
            } else {
                $socialUser = Socialite::driver($provider)->user();
            }

            // If there is no email or name address then error
            if (!$socialUser->getEmail() || !$socialUser->getName()) {
                return redirect()->route('login')
                    ->with(['toast' => ['type' => 'danger', 'title' => __('Unable to fetch your email address or name.'), 'milliseconds' => 7000]]);
            }

            $socialAccount = SocialAccount::where('provider', $provider)->where('provider_id', $socialUser->getId())->first();
            $user = $socialAccount?->user;
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
            }

            if ($user) {
                if ($socialAccount) {
                    $socialAccount->update([
                        'name' => $socialUser->getName(),
                        'nickname' => $socialUser->getNickname(),
                        'email' => $socialUser->getEmail(),
                        'avatar_path' => $socialUser->getAvatar(),
                        'extra' => $socialUser?->user,
                        // oauth
                        'token' => $socialUser->token,
                        // v2
                        'refresh_token' => $socialUser?->refreshToken,
                        'expires_at' => $socialUser?->expiresIn,
                        // v1
                        'secret' => $socialUser?->tokenSecret,
                    ]);
                    $user->update([
                        'last_login_at' => now(),
                        'last_login_ip' => request()->ip(),
                    ]);
                } elseif (!$socialAccount && $allowAnyProviderAuth) {
                    $socialAccount = $user->socialAccounts()->create([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'name' => $socialUser->getName(),
                        'nickname' => $socialUser->getNickname(),
                        'email' => $socialUser->getEmail(),
                        'avatar_path' => $socialUser->getAvatar(),
                        'extra' => $socialUser?->user,
                        // oauth
                        'token' => $socialUser->token,
                        // v2
                        'refresh_token' => $socialUser?->refreshToken,
                        'expires_at' => $socialUser?->expiresIn,
                        // v1
                        'secret' => $socialUser?->tokenSecret,
                    ]);
                    $user->update([
                        'last_login_at' => now(),
                        'last_login_ip' => request()->ip(),
                    ]);
                } else {
                    if ($request->wantsJson()) {
                        return response()->json(['message' => __('Provider mismatch. You used a different provider while registration.')], 422);
                    }

                    return redirect()->route('login')
                        ->with(['toast' => ['type' => 'danger', 'title' => __('Provider mismatch. You used a different provider while registration.'), 'milliseconds' => 7000]]);
                }
            } else {
                // Before creating new user check if registration feature is disabled.
                if (!Route::has('register')) {
                    return redirect()->route('login')
                        ->with(['toast' => ['type' => 'danger', 'title' => __('New user registration is disabled!'), 'milliseconds' => 5000]]);
                }

                // Create user
                $countryId = $this->geolocationService->getCountryIdFromIP(request()->ip());
                $data = [
                    'username' => Helper::uniqidReal(),
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => null,
                    'country_id' => $countryId,
                    'email_verified_at' => now(),
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                ];

                // Handle choosing of username.
                if ($socialUser->getNickname()) {
                    $username = strtolower($socialUser->getNickname());
                    $validator = Validator::make(['username' => $username], [
                        'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username'],
                    ]);
                    if ($validator->passes()) {
                        $data['username'] = $username;
                        $data['user_setup_status'] = 1;
                    }
                }

                $user = User::create($data);
                $user->assignRole(Role::DEFAULT_ROLE_NAME);
                $socialAccount = $user->socialAccounts()->create([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'name' => $socialUser->getName(),
                    'nickname' => $socialUser->getNickname(),
                    'email' => $socialUser->getEmail(),
                    'avatar_path' => $socialUser->getAvatar(),
                    'extra' => $socialUser?->user,
                    // oauth
                    'token' => $socialUser->token,
                    // v2
                    'refresh_token' => $socialUser?->refreshToken,
                    'expires_at' => $socialUser?->expiresIn,
                    // v1
                    'secret' => $socialUser?->tokenSecret,
                ]);
                event(new Registered($user));

                // Download and store avatar for new user
                if ($socialUser->getAvatar()) {
                    try {
                        $user->downloadProfilePhotoFromUrl($socialUser->getAvatar());
                    } catch (\Exception $e) {
                        Log::error("Failed to download avatar: " . $e->getMessage());
                    }
                }
            }

            // If OAuth was discord, then check if user has discord_private_channel_id , if not then create one and update user.
            if ($provider === 'discord' && !$user->discord_private_channel_id) {
                $this->updateDiscordPrivateChannel($user, $socialUser);
            }

            // If OAuth was discord, then handle if we should try joining discord server.
            if ($provider === 'discord') {
                $this->forceJoinDiscordServer($socialUser);
            }

            // Authenticate and return.
            if ($request->wantsJson()) {
                // Generate token
                $token = $user->createToken('default');

                return response()->json([
                    'token_type' => 'Bearer',
                    'access_token' => $token->plainTextToken,
                ]);
            } else {
                // Check if 2FA enabled for user then redirect to challenge.
                if (Features::enabled(Features::twoFactorAuthentication()) && $user->hasEnabledTwoFactorAuthentication()) {
                    $request->session()->put([
                        'login.id' => $user->getKey(),
                        'login.remember' => true,
                    ]);
                    TwoFactorAuthenticationChallenged::dispatch($user);
                    return $request->wantsJson()
                        ? response()->json(['two_factor' => true])
                        : redirect()->route('two-factor.login');
                }

                Auth::login($user, true);

                return redirect()->route('home');
            }
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->wantsJson()) {
                return response()->json(['message' => __('Unable to fetch user details from :provider', ['provider' => ucfirst($provider)])], 422);
            }

            return redirect()->route('login')
                ->with(['toast' => ['type' => 'danger', 'title' => __('Unable to fetch user details from :provider', ['provider' => ucfirst($provider)]), 'milliseconds' => 7000]]);
        }
    }

    private function forceJoinDiscordServer($socialUser)
    {
        $serverID = app(GeneralSettings::class)->discord_server_id;
        $botToken = config('services.discord.token');
        $forceJoinServer = config('services.discord.force_join_server');

        if (!$serverID || !$botToken || !$forceJoinServer) {
            return;
        }

        try {
            $client = new Client();
            $response = $client->put("https://discord.com/api/v10/guilds/{$serverID}/members/{$socialUser->getId()}", [
                'headers' => [
                    'Authorization' => 'Bot ' . $botToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'access_token' => $socialUser->token,
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                Log::info("User {$socialUser->getId()} joined Discord server {$serverID}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to add user to Discord server: " . $e->getMessage());
        }
    }

    private function updateDiscordPrivateChannel($user, $socialUser)
    {
        $botEnabled = config('services.discord.token');
        if (!$botEnabled) {
            return;
        }

        try {
            $channelId = app(Discord::class)->getPrivateChannel($socialUser->getId());
            $user->update([
                'discord_user_id' => $socialUser->getId(),
                'discord_private_channel_id' => $channelId,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function indexLinked(Request $request)
    {
        $user = $request->user();

        return $user->socialAccounts()->get();
    }

    public function unlinkAccount(Request $request, SocialAccount $socialAccount)
    {
        $user = $request->user();

        $user->socialAccounts()->where('id', $socialAccount->id)->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => __('Social account unlink successful')], 200);
        } else {
            return redirect()->back()->with(['toast' => ['type' => 'success', 'title' => __('Social account unlink successful')]]);
        }
    }
}

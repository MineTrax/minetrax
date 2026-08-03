<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Determine whether the captcha challenge should be enforced.
     */
    public static function isEnabled(): bool
    {
        return config('services.turnstile.enabled', false)
            && ! config('auth.disable_email_password_auth', false);
    }

    /**
     * Validate a Turnstile response token with Cloudflare.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! self::isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $secret = config('services.turnstile.secret_key');

        if (empty($secret)) {
            Log::warning('Turnstile secret key is not configured.');

            return false;
        }

        $response = Http::asForm()->post(config('services.turnstile.verify_url'), [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if ($response->failed()) {
            return false;
        }

        return $response->json('success', false);
    }
}

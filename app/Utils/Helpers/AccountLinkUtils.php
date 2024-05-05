<?php

namespace App\Utils\Helpers;

use Illuminate\Support\Facades\Cache;

class AccountLinkUtils
{
    public static function generateOtp($userId)
    {
        // Already exists?
        $exists = Cache::get('account-link-otp::user::'.$userId);
        if ($exists) {
            return $exists;
        }

        // Generate new OTP
        $otpExpiresAt = now()->addSeconds(120);
        $otp = Helper::generateNumericOtp(6);

        // Try again if this already exists in cache (for other user).
        while (Cache::has('account-link-otp::otp::'.$otp)) {
            $otp = Helper::generateNumericOtp(6);
        }

        // All good. Save in cache
        $otpData = ['otp' => $otp, 'user_id' => $userId, 'expires_at' => $otpExpiresAt];
        Cache::add('account-link-otp::user::'.$userId, $otpData, $otpExpiresAt);
        Cache::add('account-link-otp::otp::'.$otp, $otpData, $otpExpiresAt);

        return $otpData;
    }

    public static function verifyOtp($otp)
    {
        $otpData = Cache::get('account-link-otp::otp::'.$otp);
        if (! $otpData) {
            return false;
        }

        return $otpData;
    }

    public static function forgetOtp($otp)
    {
        $otpData = Cache::get('account-link-otp::otp::'.$otp);
        if (! $otpData) {
            return false;
        }

        Cache::forget('account-link-otp::otp::'.$otp);
        Cache::forget('account-link-otp::user::'.$otpData['user_id']);

        return $otpData;
    }
}

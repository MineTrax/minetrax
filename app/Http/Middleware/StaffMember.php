<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class StaffMember
{
    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $this->auth->user();

        if (!$user || !$user->isStaffMember()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Not Authorized to view content.')
                ], 403);
            }
            return redirect()->back();
        }

        $enforce2fa = config('auth.enforce_2fa_for_staff');
        if ($enforce2fa && !$user->hasEnabledTwoFactorAuthentication()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Two Factor Authentication is required to access this resource.')
                ], 403);
            }
            return redirect()->route('profile.show')
                ->with(['toast' => ['type' => 'warning', 'title' => __('Enable Two Factor Authentication'), 'body' => __('2FA should be enabled to access this resource.'), 'milliseconds' => 7000]]);
        }

        return $next($request);
    }
}

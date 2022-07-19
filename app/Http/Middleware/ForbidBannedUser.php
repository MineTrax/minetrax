<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard as StatefulGuardContract;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForbidBannedUser
{
    protected $auth;
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $this->auth->user();

        if ($user && $user->banned_at) {
            if ($this->auth instanceof StatefulGuardContract) {
                $this->auth->logout();
            }

            $errors = [
                'login' => __('This account is banned.'),
            ];
            return Inertia::render('ShowBannedPage', $errors);
        }

        return $next($request);
    }
}

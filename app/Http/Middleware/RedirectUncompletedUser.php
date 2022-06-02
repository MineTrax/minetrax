<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RedirectUncompletedUser
{
    public function __construct(protected Guard $auth) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $this->auth->user();

        if ($user && !$user->user_setup_status) {
            return Inertia::render('Auth/PostRegistrationSetupUser');
        }

        return $next($request);
    }
}

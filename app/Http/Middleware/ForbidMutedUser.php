<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

class ForbidMutedUser
{
    protected $auth;
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $this->auth->user();

        if ($user && $user->muted_at) {

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Oops! You are Muted.')
                ], 403);
            }
            return redirect()->back();
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateSanctum
{
    /**
     * Note: Dont know if we need it. Requests working without it. Check if we can remove
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->attributes->get('sanctum') !== true) {
            // not a sanctum request
            return $next($request);
        }

        // logs a user in the WEB guard
        if ($request->hasSession() && $request->session()->has('impersonated_by')) {
            Auth::onceUsingId($request->session()->get('impersonated_by'));
        }

        return $next($request);
    }
}

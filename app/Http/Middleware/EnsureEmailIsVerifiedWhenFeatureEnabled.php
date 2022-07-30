<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;

class EnsureEmailIsVerifiedWhenFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $redirectToRoute
     * @return Response|RedirectResponse|null
     */
    public function handle($request, Closure $next, string $redirectToRoute = null)
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                Features::enabled(Features::emailVerification()) &&
                ! $request->user()->hasVerifiedEmail())) {
            return $request->expectsJson()
                ? abort(403, __('Your email address is not verified.'))
                : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}

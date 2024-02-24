<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Return early if feature not enabled. i.e. no available locales.
        $availableLocales = config('app.available_locales');
        if (!$availableLocales) {
            return $next($request);
        }

        $localeInCookie = $request->cookie('locale');
        if (! $request->user() && ! $localeInCookie) {
            return $next($request);
        }

        $localeInUser = $request?->user()?->locale;
        $locale = $localeInUser ?? $localeInCookie;

        if (isset($locale) && in_array($locale, $availableLocales)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

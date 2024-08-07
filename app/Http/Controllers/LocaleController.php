<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function getAvailableLocales(Request $request)
    {
        $availableLocales = config('app.available_locales');
        $localeList = config('constants.locale_keymap');

        $localeMap = collect($availableLocales)->map(function ($locale) use ($localeList) {
            if (array_key_exists($locale, $localeList)) {
                return [
                    'code' => $locale,
                    ...$localeList[$locale],
                ];
            } else {
                return [
                    'code' => $locale,
                    'name' => $locale,
                    'iso_code' => '_unknown',
                    'display' => $locale,
                ];
            }
        });

        return response()->json($localeMap);
    }

    public function setLocale(Request $request)
    {
        $availableLocales = config('app.available_locales') ?? [];

        $request->validate([
            'locale' => 'required|string|in:'.implode(',', $availableLocales),
        ]);

        $locale = $request->input('locale');

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return redirect()->back()->withCookie(cookie()->forever('locale', $locale));
    }
}

<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\StoreCurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreCurrencyController extends Controller
{
    /**
     * Switch the active display currency.
     *
     * Stored in the session for everyone, and additionally persisted on the account for
     * logged-in users so the choice survives a new session.
     */
    public function switch(Request $request, StoreCurrencyService $currencies): RedirectResponse
    {
        abort_unless(config('store.enabled'), 404);

        $request->validate(['code' => 'required|string|size:3']);

        $currency = $currencies->find($request->input('code'));

        // Silently ignoring an unknown or disabled code rather than erroring: this is a display
        // preference, and a stale bookmark should not break the page.
        if (! $currency) {
            return redirect()->back();
        }

        session(['store_currency' => $currency->code]);

        if ($user = $request->user()) {
            // Assigned directly rather than via update(): `settings` is not in User::$fillable,
            // so a mass-assignment write would be silently discarded.
            $user->settings = array_merge($user->settings ?? [], [
                'store_currency' => $currency->code,
            ]);
            $user->save();
        }

        return redirect()->back();
    }
}

<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Settings\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Storefront, currency, tax and checkout settings.
 *
 * Payment gateways deliberately live elsewhere, at Admin -> Store -> Payment Gateways: credentials
 * are revisited far more often than any of these, and no secret should be within reach of a form
 * that exists to toggle guest checkout.
 */
class StoreSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['can:update settings']);
    }

    public function show(StoreSettings $settings)
    {
        return Inertia::render('Admin/Setting/StoreSetting', [
            'settings' => $this->present($settings),
            'currencies' => StoreCurrency::orderBy('code')->get(['code', 'name', 'is_base', 'is_enabled']),
            'hasOrders' => StoreOrder::exists(),
        ]);
    }

    public function update(Request $request, StoreSettings $settings): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_description' => ['nullable', 'string', 'max:1000'],

            'base_currency' => ['required', 'string', 'size:3'],
            'currency_rate_source' => ['required', 'string', 'in:manual,api'],

            'tax_mode' => ['required', 'string', 'in:none,inclusive,exclusive'],
            'tax_rate_bp' => ['required', 'integer', 'min:0', 'max:10000'],
            'tax_label' => ['nullable', 'string', 'max:50'],

            'enable_guest_checkout' => ['required', 'boolean'],
            'require_email_on_guest_checkout' => ['required', 'boolean'],
            'mojang_username_verification' => ['required', 'boolean'],
            'terms_text' => ['nullable', 'string', 'max:5000'],

            'show_recent_purchases' => ['required', 'boolean'],
            'hide_buyer_identity' => ['required', 'boolean'],
            'notify_staff_on_purchase' => ['required', 'boolean'],
            'auto_ban_on_chargeback' => ['required', 'boolean'],
        ]);

        // The base currency is what every historical order's base_total was computed against, so
        // changing it after the fact would silently rewrite revenue history.
        if (StoreOrder::exists() && $validated['base_currency'] !== $settings->base_currency) {
            return redirect()->back()->withErrors([
                'base_currency' => __('The base currency cannot be changed once orders exist.'),
            ]);
        }

        foreach ($validated as $key => $value) {
            $settings->{$key} = $value;
        }

        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Store Settings Updated Successfully')]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(StoreSettings $settings): array
    {
        $values = $settings->toArray();

        // Credentials live on their own screen and must never reach this one.
        unset($values['gateway_credentials'], $values['enabled_gateways']);

        return $values;
    }
}

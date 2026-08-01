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
            // exponent comes along so the goal amount can be typed as a decimal and sent as minor
            // units: JPY has no minor unit, KWD has three.
            'currencies' => StoreCurrency::orderBy('code')->get(['code', 'name', 'exponent', 'is_base', 'is_enabled']),
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

            'enable_guest_checkout' => ['required', 'boolean'],
            'require_email_on_guest_checkout' => ['required', 'boolean'],
            'mojang_username_verification' => ['required', 'boolean'],
            'terms_text' => ['nullable', 'string', 'max:5000'],

            'show_recent_purchases' => ['required', 'boolean'],
            'show_purchase_goal' => ['required', 'boolean'],
            // Minor units of the base currency, built client-side from that currency's exponent.
            // Zero means no goal, which is why min is 0 rather than 1.
            'purchase_goal_amount' => ['required', 'integer', 'min:0'],
            'show_top_donor' => ['required', 'boolean'],
            'hide_buyer_identity' => ['required', 'boolean'],
            'notify_staff_on_purchase' => ['required', 'boolean'],
            // Restricted to Discord's own host: the URL is posted to server-side, so anything else
            // here would make this form an outbound request to wherever an admin was told to paste.
            'discord_purchase_webhook_url' => ['nullable', 'url', 'max:255', 'starts_with:https://discord.com/api/webhooks/,https://discordapp.com/api/webhooks/'],
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
        // Gateways and their credentials are rows of their own, on their own screen. Nothing to
        // strip here any more.
        return $settings->toArray();
    }
}

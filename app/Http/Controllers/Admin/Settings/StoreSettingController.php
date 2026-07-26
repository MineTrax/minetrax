<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\StorePaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Models\StoreCurrency;
use App\Models\StoreOrder;
use App\Settings\StoreSettings;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreSettingController extends Controller
{
    /**
     * Sent in place of a stored secret so credentials never round-trip to the browser. A field
     * that comes back unchanged is left exactly as it was.
     */
    private const SECRET_MASK = '********';

    public function __construct(private StorePaymentGatewayManager $gateways)
    {
        $this->middleware(['can:update settings']);
    }

    public function show(StoreSettings $settings)
    {
        return Inertia::render('Admin/Setting/StoreSetting', [
            'settings' => $this->present($settings),
            'gateways' => $this->gateways->all()
                ->map(fn (StorePaymentGatewayContract $driver) => [
                    'key' => $driver->gateway()->value,
                    'label' => $driver->label(),
                    'description' => $driver->description(),
                    'is_configured' => $driver->isEnabled(),
                    'schema' => $driver->settingsSchema(),
                    'credentials' => $this->maskedCredentials($driver, $settings),
                ])->values(),
            'currencies' => StoreCurrency::orderBy('code')->get(['code', 'name', 'is_base', 'is_enabled']),
            'hasOrders' => StoreOrder::exists(),
            'webhookUrlTemplate' => route('api.store.webhook', ['gateway' => '__GATEWAY__']),
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

            'enabled_gateways' => ['present', 'array'],
            'enabled_gateways.*' => ['string', 'in:'.implode(',', array_keys(config('store.gateways', [])))],

            'gateway_credentials' => ['present', 'array'],

            'show_recent_purchases' => ['required', 'boolean'],
            'hide_buyer_identity' => ['required', 'boolean'],
            'notify_staff_on_purchase' => ['required', 'boolean'],
        ]);

        // The base currency is what every historical order's base_total was computed against, so
        // changing it after the fact would silently rewrite revenue history.
        if (StoreOrder::exists() && $validated['base_currency'] !== $settings->base_currency) {
            return redirect()->back()->withErrors([
                'base_currency' => __('The base currency cannot be changed once orders exist.'),
            ]);
        }

        foreach ($validated as $key => $value) {
            if ($key !== 'gateway_credentials') {
                $settings->{$key} = $value;
            }
        }

        $settings->gateway_credentials = $this->mergeCredentials($request->input('gateway_credentials', []), $settings);
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

        // Never send the raw bag; the per-gateway masked copy is what the form binds to.
        unset($values['gateway_credentials']);

        return $values;
    }

    /**
     * @return array<string, mixed>
     */
    private function maskedCredentials(StorePaymentGatewayContract $driver, StoreSettings $settings): array
    {
        $stored = data_get($settings->gateway_credentials, $driver->gateway()->value, []);
        $out = [];

        foreach ($driver->settingsSchema() as $field) {
            $value = $stored[$field['key']] ?? null;

            $out[$field['key']] = ($field['secret'] ?? false) && filled($value)
                ? self::SECRET_MASK
                : $value;
        }

        return $out;
    }

    /**
     * Fold the submitted credentials back into the stored bag.
     *
     * A secret that comes back as the mask was never shown to the browser in the first place, so
     * it is kept rather than overwritten with asterisks. Only fields the driver declares are
     * stored, so a crafted request cannot stuff arbitrary keys into the encrypted bag.
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, mixed>
     */
    private function mergeCredentials(array $submitted, StoreSettings $settings): array
    {
        $merged = [];

        foreach ($this->gateways->all() as $key => $driver) {
            $stored = data_get($settings->gateway_credentials, $key, []);
            $incoming = (array) ($submitted[$key] ?? []);
            $bag = [];

            foreach ($driver->settingsSchema() as $field) {
                $name = $field['key'];
                $value = $incoming[$name] ?? null;

                $bag[$name] = ($field['secret'] ?? false) && $value === self::SECRET_MASK
                    ? ($stored[$name] ?? null)
                    : $value;
            }

            $merged[$key] = array_filter($bag, fn ($value) => $value !== null && $value !== '');
        }

        return $merged;
    }
}

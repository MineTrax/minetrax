<?php

namespace App\Http\Controllers\Admin\Store;

use App\Contracts\StorePaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Models\StoreCurrency;
use App\Settings\StoreSettings;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Enable, disable and configure payment gateways.
 *
 * Its own section rather than a block inside Store Settings: credentials are the one part of the
 * module an owner comes back to repeatedly (rotating a key, adding a provider), and burying them
 * under a page of tax and checkout toggles makes that harder than it needs to be.
 *
 * The page renders itself entirely from each driver's settingsSchema(), so a new gateway appears
 * here — fields, webhook URL and all — the moment its config line lands.
 */
class StorePaymentGatewayController extends Controller
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

    public function index(StoreSettings $settings): Response
    {
        $enabledCurrencies = StoreCurrency::enabled()->orderBy('code')->pluck('code');

        return Inertia::render('Admin/StorePaymentGateway/IndexStorePaymentGateway', [
            'gateways' => $this->gateways->all()
                ->map(fn (StorePaymentGatewayContract $driver) => [
                    'key' => $driver->gateway()->value,
                    'label' => $driver->label(),
                    'description' => $driver->description(),
                    'is_enabled' => in_array($driver->gateway()->value, $settings->enabled_gateways ?? [], true),
                    'is_configured' => $driver->isEnabled(),
                    'schema' => $driver->settingsSchema(),
                    'credentials' => $this->maskedCredentials($driver, $settings),
                    'webhook_url' => route('api.store.webhook', ['gateway' => $driver->gateway()->value]),
                    // Null means "any". Anything else is listed so an owner can see at a glance
                    // why a gateway is not being offered for one of their currencies.
                    'supported_currencies' => $driver->supportedCurrencies(),
                    'unsupported_currencies' => $this->unsupportedCurrencies($driver, $enabledCurrencies->all()),
                ])->values(),
            'enabledCurrencies' => $enabledCurrencies,
        ]);
    }

    public function update(Request $request, StoreSettings $settings): RedirectResponse
    {
        $validated = $request->validate([
            'enabled_gateways' => ['present', 'array'],
            'enabled_gateways.*' => ['string', 'in:'.implode(',', array_keys(config('store.gateways', [])))],
            'gateway_credentials' => ['present', 'array'],
        ]);

        $settings->enabled_gateways = $validated['enabled_gateways'];
        $settings->gateway_credentials = $this->mergeCredentials($request->input('gateway_credentials', []), $settings);
        $settings->save();

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Payment Gateways Updated')]]);
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
     * A gateway missing from the submission entirely is left exactly as it was. Switching a
     * gateway off is not the same as forgetting its keys, and an owner who turns Stripe off for an
     * afternoon should not have to dig the credentials out again afterwards.
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, mixed>
     */
    private function mergeCredentials(array $submitted, StoreSettings $settings): array
    {
        $merged = [];

        foreach ($this->gateways->all() as $key => $driver) {
            $stored = data_get($settings->gateway_credentials, $key, []);

            if (! array_key_exists($key, $submitted)) {
                $merged[$key] = $stored;

                continue;
            }

            $incoming = (array) $submitted[$key];
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

    /**
     * Enabled store currencies this driver cannot charge in.
     *
     * @param  array<int, string>  $enabledCurrencies
     * @return array<int, string>
     */
    private function unsupportedCurrencies(StorePaymentGatewayContract $driver, array $enabledCurrencies): array
    {
        $supported = $driver->supportedCurrencies();

        if ($supported === null) {
            return [];
        }

        $supported = array_map('strtoupper', $supported);

        return array_values(array_filter(
            $enabledCurrencies,
            fn (string $code) => ! in_array(strtoupper($code), $supported, true)
        ));
    }
}

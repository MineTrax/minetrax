<?php

namespace App\Http\Controllers\Admin\Store;

use App\Contracts\StorePaymentGatewayContract;
use App\Http\Controllers\Controller;
use App\Models\StoreCurrency;
use App\Models\StorePaymentGateway as GatewayRecord;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function index(): Response
    {
        $enabledCurrencies = StoreCurrency::enabled()->orderBy('code')->pluck('code');
        $records = $this->records();

        return Inertia::render('Admin/StorePaymentGateway/IndexStorePaymentGateway', [
            'gateways' => $this->gateways->all()
                ->map(fn (StorePaymentGatewayContract $driver) => [
                    'key' => $driver->gateway()->value,
                    'label' => $driver->label(),
                    'description' => $driver->description(),
                    'is_enabled' => (bool) $records->get($driver->gateway()->value)?->is_enabled,
                    'is_configured' => $driver->isEnabled(),
                    'schema' => $driver->settingsSchema(),
                    'credentials' => $this->maskedCredentials($driver, $records),
                    'webhook_url' => route('api.store.webhook', ['gateway' => $driver->gateway()->value]),
                    // Null means "any". Anything else is listed so an owner can see at a glance
                    // why a gateway is not being offered for one of their currencies.
                    'supported_currencies' => $driver->supportedCurrencies(),
                    'unsupported_currencies' => $this->unsupportedCurrencies($driver, $enabledCurrencies->all()),
                ])->values(),
            'enabledCurrencies' => $enabledCurrencies,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled_gateways' => ['present', 'array'],
            'enabled_gateways.*' => ['string', 'in:'.implode(',', array_keys(config('store.gateways', [])))],
            'gateway_credentials' => ['present', 'array'],
            'gateway_credentials.*' => ['array'],
            // Capped because one of these fields is now a rich text editor, so its value is markup
            // rather than a key. The whole bag is JSON-encoded and encrypted into a single TEXT
            // column, and encryption inflates it — an uncapped field would fail at the database
            // rather than at the form.
            'gateway_credentials.*.*' => ['nullable', 'string', 'max:20000'],
        ]);

        $records = $this->records();
        $submitted = $request->input('gateway_credentials', []);

        // One transaction: a half-applied save could leave a gateway switched on with the previous
        // owner's keys, which is a worse state than either the old one or the new one.
        DB::transaction(function () use ($validated, $submitted, $records) {
            foreach ($this->gateways->all() as $key => $driver) {
                $record = $records->get($key) ?? new GatewayRecord(['key' => $key, 'credentials' => []]);

                $record->is_enabled = in_array($key, $validated['enabled_gateways'], true);
                $record->credentials = $this->mergedCredentialsFor($driver, $submitted, $record);
                $record->save();
            }
        });

        return redirect()->back()
            ->with(['toast' => ['type' => 'success', 'title' => __('Payment Gateways Updated')]]);
    }

    /**
     * Every gateway's stored row, keyed by gateway key.
     *
     * @return Collection<string, GatewayRecord>
     */
    private function records(): Collection
    {
        return GatewayRecord::orderBy('sort_order')->orderBy('id')->get()->keyBy('key');
    }

    /**
     * @param  Collection<string, GatewayRecord>  $records
     * @return array<string, mixed>
     */
    private function maskedCredentials(StorePaymentGatewayContract $driver, Collection $records): array
    {
        $stored = $records->get($driver->gateway()->value)?->credentials ?? [];
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
     * Fold one gateway's submitted credentials into what it already had.
     *
     * A secret that comes back as the mask was never shown to the browser in the first place, so
     * it is kept rather than overwritten with asterisks. Only fields the driver declares are
     * stored, so a crafted request cannot stuff arbitrary keys into the encrypted column.
     *
     * A gateway missing from the submission entirely keeps exactly what it had. Switching a gateway
     * off is not the same as forgetting its keys, and an owner who turns Stripe off for an
     * afternoon should not have to dig the credentials out again afterwards.
     *
     * @param  array<string, mixed>  $submitted  Every gateway's fields, keyed by gateway
     * @return array<string, mixed>
     */
    private function mergedCredentialsFor(
        StorePaymentGatewayContract $driver,
        array $submitted,
        GatewayRecord $record,
    ): array {
        $key = $driver->gateway()->value;
        $stored = $record->credentials ?? [];

        if (! array_key_exists($key, $submitted)) {
            return $stored;
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

        return array_filter($bag, fn ($value) => $value !== null && $value !== '');
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

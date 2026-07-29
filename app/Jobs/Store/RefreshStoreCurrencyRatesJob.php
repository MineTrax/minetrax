<?php

namespace App\Jobs\Store;

use App\Models\StoreCurrency;
use App\Settings\StoreSettings;
use App\Utils\ExchangeRates\StoreExchangeRateProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Brings each enabled currency's rate up to date, once a day.
 *
 * Only computed prices move: a package with an explicit per-currency override is priced exactly as
 * the admin typed it, and an order that already exists keeps the rate it snapshotted. So a rate
 * change alters what the storefront asks for tomorrow, never what history says was charged.
 *
 * Every failure path deliberately leaves the last known rate in place. A currency priced at a rate
 * of zero would give the whole catalogue away, which is far worse than a rate being a day stale.
 */
class RefreshStoreCurrencyRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('longtask');
    }

    public function handle(StoreSettings $settings, StoreExchangeRateProviderManager $providers): void
    {
        if ($settings->currency_rate_source !== 'api') {
            // The admin sets rates by hand, so an automatic refresh would silently overwrite them.
            return;
        }

        $provider = $providers->active();

        if (! $provider || ! $provider->isConfigured()) {
            Log::warning('Store rate refresh skipped: no configured exchange rate provider.', [
                'configured' => config('store.rate_provider'),
            ]);

            return;
        }

        $base = StoreCurrency::firstWhere('is_base', true);

        if (! $base) {
            Log::warning('Store rate refresh skipped: no base currency.');

            return;
        }

        // The base is always 1 by definition and is never asked for; a disabled currency is not
        // being sold in, so there is nothing to keep current.
        $targets = StoreCurrency::where('is_enabled', true)
            ->where('is_base', false)
            ->pluck('code')
            ->all();

        if ($targets === []) {
            return;
        }

        try {
            $rates = $provider->ratesFor($base->code, $targets);
        } catch (\Throwable $exception) {
            Log::error('Store rate refresh failed; keeping the last known rates.', [
                'provider' => $provider->key(),
                'base' => $base->code,
                'exception' => $exception->getMessage(),
            ]);

            return;
        }

        $updated = 0;

        foreach ($targets as $code) {
            $rate = $rates[strtoupper($code)] ?? null;

            if ($rate === null || ! is_numeric($rate) || (float) $rate <= 0) {
                continue;
            }

            StoreCurrency::where('code', $code)->update([
                'rate_to_base' => $rate,
                'rate_updated_at' => now(),
                'updated_at' => now(),
            ]);

            $updated++;
        }

        $missing = array_values(array_diff(
            array_map('strtoupper', $targets),
            array_keys($rates)
        ));

        if ($missing !== []) {
            // Named rather than counted: an admin looking at a stale rate needs to know the feed
            // does not carry that currency at all, and that it is theirs to maintain by hand.
            Log::info('Store rate refresh: provider does not carry some enabled currencies.', [
                'provider' => $provider->key(),
                'currencies' => $missing,
            ]);
        }

        Log::info('Store rate refresh complete.', [
            'provider' => $provider->key(),
            'base' => $base->code,
            'updated' => $updated,
        ]);
    }
}

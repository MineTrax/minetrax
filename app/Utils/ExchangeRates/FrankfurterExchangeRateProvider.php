<?php

namespace App\Utils\ExchangeRates;

use App\Contracts\StoreExchangeRateProviderContract;
use Illuminate\Support\Facades\Http;

/**
 * frankfurter.app — European Central Bank reference rates, free and unauthenticated.
 *
 * Chosen as the default because it needs no account and no key, so multi-currency works on a fresh
 * install with nothing to configure. It carries the ~30 currencies the ECB publishes; anything
 * outside that comes back absent and keeps whatever rate the admin last set by hand.
 */
class FrankfurterExchangeRateProvider implements StoreExchangeRateProviderContract
{
    private const ENDPOINT = 'https://api.frankfurter.app/latest';

    public function key(): string
    {
        return 'frankfurter';
    }

    public function label(): string
    {
        return 'Frankfurter (European Central Bank)';
    }

    public function isConfigured(): bool
    {
        // Nothing to configure, which is the point of it.
        return true;
    }

    /**
     * @param  array<int, string>  $currencyCodes
     * @return array<string, string>
     */
    public function ratesFor(string $baseCode, array $currencyCodes): array
    {
        if ($currencyCodes === []) {
            return [];
        }

        $response = Http::timeout(15)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->get(self::ENDPOINT, [
                'base' => strtoupper($baseCode),
                'symbols' => implode(',', array_map('strtoupper', $currencyCodes)),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Frankfurter returned HTTP '.$response->status().' for base '.$baseCode.'.'
            );
        }

        $rates = $response->json('rates');

        if (! is_array($rates) || $rates === []) {
            throw new \RuntimeException('Frankfurter returned no rates for base '.$baseCode.'.');
        }

        $result = [];

        foreach ($rates as $code => $rate) {
            // A non-positive rate would price the whole store at zero, so it is dropped rather than
            // written. Cast through string to keep the feed's precision intact.
            if (is_numeric($rate) && (float) $rate > 0) {
                $result[strtoupper((string) $code)] = (string) $rate;
            }
        }

        return $result;
    }
}

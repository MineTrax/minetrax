<?php

namespace App\Contracts;

/**
 * Where daily exchange rates come from.
 *
 * A provider is one class plus one line in config/store.php -> rate_providers, the same shape the
 * payment gateways use. Swapping frankfurter.app for a paid feed is therefore a new file, not a
 * change to the job, the settings or the currency table.
 */
interface StoreExchangeRateProviderContract
{
    /**
     * The registry key, matching its entry in config/store.php.
     */
    public function key(): string;

    /**
     * Human-readable name, for logs and the admin currency screen.
     */
    public function label(): string;

    /**
     * Whether the provider has everything it needs to be called. Providers that want an API key
     * report false until it is set, so a misconfigured feed is a skipped run rather than a failed one.
     */
    public function isConfigured(): bool;

    /**
     * How many units of each requested currency equal one unit of the base currency.
     *
     * The return shape matches `store_currencies.rate_to_base` exactly — USD -> JPY at 151.2 means
     * one dollar buys 151.2 yen. Rates come back as strings so a provider's precision survives
     * being put in a decimal(20,10) column; a float would round it on the way past.
     *
     * A currency the provider does not carry is simply absent from the result rather than zero: the
     * job leaves the last known rate in place, because a zero rate would make everything free.
     *
     * @param  array<int, string>  $currencyCodes
     * @return array<string, string>
     *
     * @throws \RuntimeException when the feed cannot be reached or answers with something unusable
     */
    public function ratesFor(string $baseCode, array $currencyCodes): array;
}

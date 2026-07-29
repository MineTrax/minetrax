<?php

namespace App\Utils\ExchangeRates;

use App\Contracts\StoreExchangeRateProviderContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

/**
 * Resolves rate providers from the config registry, exactly as StorePaymentGatewayManager does for
 * gateways: adding a feed is one class plus one line in config/store.php.
 */
class StoreExchangeRateProviderManager
{
    /** @var array<string, StoreExchangeRateProviderContract> */
    private array $resolved = [];

    public function __construct(private Container $container) {}

    /**
     * @return Collection<string, StoreExchangeRateProviderContract>
     */
    public function all(): Collection
    {
        return collect(config('store.rate_providers', []))
            ->keys()
            ->mapWithKeys(fn (string $key) => [$key => $this->provider($key)])
            ->filter();
    }

    public function provider(?string $key): ?StoreExchangeRateProviderContract
    {
        if (! $key) {
            return null;
        }

        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $class = config("store.rate_providers.{$key}");

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return $this->resolved[$key] = $this->container->make($class);
    }

    /**
     * The provider the install is configured to use.
     */
    public function active(): ?StoreExchangeRateProviderContract
    {
        return $this->provider(config('store.rate_provider'));
    }

    /**
     * @throws \RuntimeException when the key is unknown
     */
    public function providerOrFail(string $key): StoreExchangeRateProviderContract
    {
        $provider = $this->provider($key);

        if (! $provider) {
            throw new \RuntimeException("Unknown store exchange rate provider [{$key}].");
        }

        return $provider;
    }
}

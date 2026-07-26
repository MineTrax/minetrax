<?php

namespace App\Utils\Payments;

use App\Contracts\StorePaymentGatewayContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

/**
 * Resolves gateway drivers from the config registry.
 *
 * Deliberately not a Laravel Manager: that pattern needs a createFooDriver() method per gateway,
 * which is exactly the per-gateway edit this design exists to remove. Here a new driver is one
 * class plus one line in config/store.php.
 */
class StorePaymentGatewayManager
{
    /** @var array<string, StorePaymentGatewayContract> */
    private array $resolved = [];

    public function __construct(private Container $container) {}

    /**
     * Every registered driver, configured or not.
     *
     * @return Collection<string, StorePaymentGatewayContract>
     */
    public function all(): Collection
    {
        return collect(config('store.gateways', []))
            ->keys()
            ->mapWithKeys(fn (string $key) => [$key => $this->driver($key)])
            ->filter();
    }

    /**
     * Drivers the admin has switched on and fully configured.
     *
     * @return Collection<string, StorePaymentGatewayContract>
     */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (StorePaymentGatewayContract $driver) => $driver->isEnabled());
    }

    /**
     * Drivers that can actually charge the given currency. A gateway that cannot is never
     * offered, rather than failing once the buyer is already on its page.
     *
     * @return Collection<string, StorePaymentGatewayContract>
     */
    public function availableFor(string $currencyCode): Collection
    {
        return $this->enabled()->filter(function (StorePaymentGatewayContract $driver) use ($currencyCode) {
            $supported = $driver->supportedCurrencies();

            return $supported === null
                || in_array(strtoupper($currencyCode), array_map('strtoupper', $supported), true);
        });
    }

    public function driver(?string $key): ?StorePaymentGatewayContract
    {
        if (! $key) {
            return null;
        }

        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $class = config("store.gateways.{$key}");

        if (! $class || ! class_exists($class)) {
            return null;
        }

        $driver = $this->container->make($class);

        return $this->resolved[$key] = $driver;
    }

    /**
     * @throws \RuntimeException when the key is unknown or the driver is not usable
     */
    public function driverOrFail(string $key): StorePaymentGatewayContract
    {
        $driver = $this->driver($key);

        if (! $driver) {
            throw new \RuntimeException("Unknown store payment gateway [{$key}].");
        }

        return $driver;
    }

    public function has(string $key): bool
    {
        return $this->driver($key) !== null;
    }
}

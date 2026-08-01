<?php

namespace Tests;

use App\Models\StoreCurrency;
use App\Models\StorePaymentGateway;
use App\Utils\Payments\StorePaymentGatewayManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable exception handling here for debugging errors if needed
        // $this->withoutExceptionHandling();
    }

    /**
     * The store's base currency.
     *
     * DatabaseSeeder seeds one, because a store with no base currency is not a usable state.
     * Tests therefore reuse that row rather than creating a second USD and tripping the unique
     * index on `code`.
     */
    protected function baseCurrency(): StoreCurrency
    {
        return StoreCurrency::firstWhere('is_base', true)
            ?? StoreCurrency::factory()->base()->create();
    }

    /**
     * Switch exactly these gateways on and everything else off.
     *
     * StorePaymentGatewaySeeder gives every configured driver a row, so this only flips toggles.
     * Credentials are optional and merge into whatever the row already has, which is how a test
     * gets a driver past its required-field check without a real account.
     *
     * @param  array<int, string>  $keys
     * @param  array<string, array<string, mixed>>  $credentials  Keyed by gateway
     */
    protected function enableStoreGateways(array $keys, array $credentials = []): void
    {
        StorePaymentGateway::query()->update(['is_enabled' => false]);

        foreach ($keys as $key) {
            $record = StorePaymentGateway::firstOrNew(['key' => $key]);
            $record->is_enabled = true;
            $record->credentials = array_merge($record->credentials ?? [], $credentials[$key] ?? []);
            $record->save();
        }

        // The manager is a singleton and each driver caches its row for the life of the request, so
        // a driver resolved before this call would still be answering from the old configuration.
        // A real request would have resolved them fresh; this makes the test behave the same way.
        $this->app->forgetInstance(StorePaymentGatewayManager::class);
    }
}

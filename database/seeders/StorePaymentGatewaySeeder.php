<?php

namespace Database\Seeders;

use App\Models\StorePaymentGateway;
use Illuminate\Database\Seeder;

/**
 * Gives every gateway the application ships a driver for a row to be configured through.
 *
 * Driven by `config('store.gateways')` rather than a hardcoded list, so adding a provider is still
 * one class plus one config line — this seeder picks it up on the next run with no edit here.
 *
 * Strictly additive. An existing row is never touched: re-running this after adding a fourth
 * gateway creates that one row and leaves the other three, credentials and toggles intact. That is
 * the whole reason it is firstOrCreate rather than updateOrCreate — an upsert would quietly switch
 * a live gateway back off on every deploy.
 */
class StorePaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        // Manual is on by default so a fresh install has a working checkout without credentials.
        // Everything else needs keys before it can charge anything, so it starts off.
        $defaultsOn = ['manual'];

        foreach (array_keys(config('store.gateways', [])) as $index => $key) {
            StorePaymentGateway::firstOrCreate(
                ['key' => $key],
                [
                    'is_enabled' => in_array($key, $defaultsOn, true),
                    'credentials' => [],
                    // Config order is the order an owner sees them in until they reorder.
                    'sort_order' => $index,
                ],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\StoreCurrency;
use App\Services\StoreCurrencyService;
use App\Settings\StoreSettings;
use Illuminate\Database\Seeder;

/**
 * Gives the store its base currency row.
 *
 * Exactly one currency must be the base. Zero is not a valid state: with an empty table the
 * currency picker in Store Settings has nothing to offer, and the storefront falls back to a
 * transient record that exists only for the length of a request, so nothing can be priced or
 * enabled through the admin UI.
 *
 * Idempotent, and it never demotes an existing base — an install that already picked one keeps it.
 */
class StoreCurrencySeeder extends Seeder
{
    public function run(): void
    {
        if (StoreCurrency::where('is_base', true)->exists()) {
            return;
        }

        $currencies = app(StoreCurrencyService::class);
        $code = strtoupper(app(StoreSettings::class)->base_currency ?: 'USD');

        // An existing row for this code just gets promoted rather than duplicated; `code` is
        // unique and the admin may well have created it by hand already.
        $existing = StoreCurrency::where('code', $code)->first();

        if ($existing) {
            $existing->update(['is_base' => true, 'rate_to_base' => 1, 'is_enabled' => true]);

            return;
        }

        StoreCurrency::create([
            'code' => $code,
            'name' => $currencies->nameFor($code),
            'symbol' => $currencies->defaultSymbolFor($code),
            'symbol_position' => 'prefix',
            // From brick/money's ISO-4217 table, never assumed to be 2.
            'exponent' => $currencies->exponentFor($code),
            'rate_to_base' => 1,
            'is_base' => true,
            'is_enabled' => true,
            'price_rounding' => 'none',
            'sort_order' => 0,
        ]);
    }
}

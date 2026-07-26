<?php

namespace App\Services;

use App\Enums\StorePriceRounding;
use App\Models\StoreCurrency;
use App\Models\StorePackage;
use App\Settings\StoreSettings;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;
use Brick\Money\Currency;
use Brick\Money\Money;
use Illuminate\Support\Collection;

/**
 * The single place a monetary amount is built, converted, rounded or formatted.
 *
 * Every amount in the Store is an integer in the currency's minor unit. That unit is NOT always
 * 1/100: JPY has no minor unit at all and KWD has three digits, so a literal `* 100` would
 * overcharge a Japanese buyer 100x and undercharge a Kuwaiti one. brick/money owns the ISO-4217
 * exponent table, so amounts are only ever produced through it.
 */
class StoreCurrencyService
{
    private ?StoreCurrency $baseCache = null;

    private ?Collection $enabledCache = null;

    public function __construct(private StoreSettings $settings) {}

    /**
     * The reporting currency. Falls back to a transient record built from settings when the
     * currency table has not been populated yet, so the store still works out of the box.
     */
    public function base(): StoreCurrency
    {
        if ($this->baseCache) {
            return $this->baseCache;
        }

        $base = StoreCurrency::where('is_base', true)->first();

        if (! $base) {
            $code = $this->settings->base_currency;
            $base = new StoreCurrency([
                'code' => $code,
                'name' => $code,
                'symbol' => $this->defaultSymbolFor($code),
                'symbol_position' => 'prefix',
                'exponent' => $this->exponentFor($code),
                'rate_to_base' => 1,
                'is_base' => true,
                'is_enabled' => true,
                'price_rounding' => StorePriceRounding::NONE,
            ]);
        }

        return $this->baseCache = $base;
    }

    /** @return Collection<int, StoreCurrency> */
    public function enabled(): Collection
    {
        return $this->enabledCache ??= StoreCurrency::enabled()->orderBy('sort_order')->orderBy('code')->get();
    }

    public function find(?string $code): ?StoreCurrency
    {
        if (! $code) {
            return null;
        }

        return $this->enabled()->firstWhere('code', strtoupper($code));
    }

    /**
     * Resolve the currency for the current visitor.
     *
     * Order: explicit switcher choice, then the user's saved preference, then their geolocated
     * country, then the base currency. Only enabled currencies are ever returned.
     */
    public function resolve(): StoreCurrency
    {
        if ($currency = $this->find(session('store_currency'))) {
            return $currency;
        }

        if ($user = auth()->user()) {
            if ($currency = $this->find(data_get($user->settings, 'store_currency'))) {
                return $currency;
            }
        }

        if ($currency = $this->forCountry(auth()->user()?->country?->iso_code)) {
            return $currency;
        }

        return $this->base();
    }

    /**
     * The currency configured for a country ISO code, if any currency claims it.
     */
    public function forCountry(?string $isoCode): ?StoreCurrency
    {
        if (! $isoCode) {
            return null;
        }

        $isoCode = strtoupper($isoCode);

        return $this->enabled()->first(
            fn (StoreCurrency $currency) => in_array($isoCode, array_map('strtoupper', $currency->country_codes ?? []), true)
        );
    }

    /**
     * Convert between two currencies via their rate to base.
     *
     * `rate_to_base` reads as "how many of this currency equal one unit of the base currency",
     * so USD->JPY at 150 turns $1 into ¥150.
     */
    public function convert(int $amountMinor, StoreCurrency $from, StoreCurrency $to): int
    {
        if ($from->code === $to->code) {
            return $amountMinor;
        }

        $inBase = $this->toBase($amountMinor, $from);

        if ($to->is_base) {
            return $inBase;
        }

        return $this->money($inBase, $this->base()->code)
            ->convertedTo($to->code, (string) $to->rate_to_base, roundingMode: RoundingMode::HalfUp)
            ->getMinorAmount()
            ->toInt();
    }

    public function toBase(int $amountMinor, StoreCurrency $from): int
    {
        if ($from->is_base || $from->code === $this->base()->code) {
            return $amountMinor;
        }

        $rate = (float) $from->rate_to_base;
        if ($rate <= 0) {
            return $amountMinor;
        }

        // Dividing by the rate rather than multiplying: rate_to_base is base -> currency.
        return $this->money($amountMinor, $from->code)
            ->convertedTo($this->base()->code, (string) (1 / $rate), roundingMode: RoundingMode::HalfUp)
            ->getMinorAmount()
            ->toInt();
    }

    public function fromBase(int $amountMinor, StoreCurrency $to): int
    {
        return $this->convert($amountMinor, $this->base(), $to);
    }

    /**
     * Apply the currency's psychological-pricing rule. Only ever applied to a converted price;
     * an explicit per-package override is stored exactly as the admin entered it.
     */
    public function applyRounding(int $amountMinor, StoreCurrency $currency): int
    {
        $unit = 10 ** $currency->exponent;

        return match ($currency->price_rounding) {
            StorePriceRounding::NEAREST_WHOLE => (int) (round($amountMinor / $unit) * $unit),
            StorePriceRounding::NEAREST_HALF => (int) (round($amountMinor / ($unit / 2)) * ($unit / 2)),
            // e.g. 1234 -> 1299 for a two-decimal currency. Meaningless with no minor unit.
            StorePriceRounding::CHARM_99 => $currency->exponent === 0
                ? (int) (round($amountMinor / $unit) * $unit)
                : (int) (floor($amountMinor / $unit) * $unit + ($unit - 1)),
            default => $amountMinor,
        };
    }

    /**
     * The price of a package in a given currency: an explicit override when one exists, otherwise
     * the base price converted and rounded.
     */
    public function priceForPackage(StorePackage $package, ?StoreCurrency $currency = null): int
    {
        $currency ??= $this->resolve();

        if ($currency->is_base || $currency->code === $this->base()->code) {
            return $package->price;
        }

        $override = $package->relationLoaded('prices')
            ? $package->prices->firstWhere('currency_code', $currency->code)
            : $package->prices()->where('currency_code', $currency->code)->first();

        if ($override) {
            return (int) $override->price;
        }

        return $this->applyRounding($this->fromBase($package->price, $currency), $currency);
    }

    /**
     * Minor units -> a plain decimal string, e.g. 999 -> "9.99", 1000 JPY -> "1000".
     */
    public function toDecimal(int $amountMinor, StoreCurrency|string $currency): string
    {
        return (string) $this->money($amountMinor, $this->codeOf($currency))->getAmount();
    }

    /**
     * A decimal amount -> minor units. Throws if the value has more precision than the currency
     * allows, rather than silently truncating money.
     */
    public function toMinor(string|int|float $amount, StoreCurrency|string $currency): int
    {
        return Money::of((string) $amount, $this->codeOf($currency), roundingMode: RoundingMode::HalfUp)
            ->getMinorAmount()
            ->toInt();
    }

    /**
     * Human-readable amount using the currency's own symbol and position, e.g. "$9.99" or "9,99 €".
     */
    public function format(int $amountMinor, StoreCurrency|string|null $currency = null): string
    {
        $currency = is_string($currency) ? ($this->find($currency) ?? $this->base()) : ($currency ?? $this->resolve());

        $decimal = $this->toDecimal($amountMinor, $currency);
        $formatted = number_format((float) $decimal, $currency->exponent, '.', ',');

        return $currency->symbol_position === 'suffix'
            ? $formatted.' '.$currency->symbol
            : $currency->symbol.$formatted;
    }

    /**
     * Both representations at once, so Vue never has to do money arithmetic.
     *
     * @return array{amount: int, currency: string, formatted: string, exponent: int}
     */
    public function present(int $amountMinor, ?StoreCurrency $currency = null): array
    {
        $currency ??= $this->resolve();

        return [
            'amount' => $amountMinor,
            'currency' => $currency->code,
            'formatted' => $this->format($amountMinor, $currency),
            'exponent' => $currency->exponent,
        ];
    }

    /**
     * ISO-4217 minor unit digits for a code, straight from brick/money.
     */
    public function exponentFor(string $code): int
    {
        try {
            return Currency::of(strtoupper($code))->getDefaultFractionDigits();
        } catch (\Throwable) {
            return 2;
        }
    }

    private function money(int $amountMinor, string $code): Money
    {
        try {
            return Money::ofMinor($amountMinor, strtoupper($code));
        } catch (\Throwable) {
            // Unknown to the ISO table (a custom code): fall back to an explicit context so the
            // amount is still exact rather than silently reinterpreted.
            return Money::ofMinor($amountMinor, Currency::create(strtoupper($code), 0, strtoupper($code), 2), new CustomContext(2));
        }
    }

    private function codeOf(StoreCurrency|string $currency): string
    {
        return is_string($currency) ? strtoupper($currency) : $currency->code;
    }

    private function defaultSymbolFor(string $code): string
    {
        return match (strtoupper($code)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            default => strtoupper($code).' ',
        };
    }
}

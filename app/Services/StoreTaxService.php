<?php

namespace App\Services;

use App\Models\StoreTax;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Which tax a buyer pays, and how much of their money it is.
 *
 * ## How the rules resolve
 *
 * Exactly one rule applies to an order: the buyer's own country if it has one, otherwise the
 * country-less fallback, otherwise no tax at all.
 *
 * Rules deliberately do **not** stack. Real jurisdictions do stack — Canada charges federal GST
 * alongside a provincial PST, and a US buyer can owe state, county and city sales tax on one
 * purchase — but every one of those is decided *below* the country level, and the only thing this
 * application knows about a buyer's location is the country its IP geolocated to. Summing two
 * country-level rules would not reproduce Canada's GST + PST; it would just charge Canadians twice.
 * Anyone needing sub-national accuracy needs a tax service with address validation behind it, which
 * is a different product.
 *
 * ## Inclusive and exclusive
 *
 * Per rule, not per store, because the convention is regional and a store sells into many regions
 * at once. The EU, UK and Australia require advertised prices to include the tax, so the figure is
 * *extracted* from the price the buyer already saw. The US adds sales tax at the till, so the figure
 * is *added* to it. The same $10 package is therefore $10.00 to a Berlin buyer with 19% German VAT
 * inside it, and $10.80 to a buyer under an 8% exclusive rule.
 *
 * ## What this is not
 *
 * It applies one rate per country and nothing else. It does not do EU B2B reverse charge (a VAT
 * -registered business buyer paying zero on production of a valid VAT number), OSS return filing,
 * US economic-nexus thresholds, or tax-exempt customer classes. Those are real obligations for a
 * store above a certain size, and their absence is a deliberate scope decision rather than an
 * oversight.
 */
class StoreTaxService
{
    /**
     * Rules change rarely and are read on every cart view and every quote, so they are cached for
     * the request rather than queried per line.
     */
    private const CACHE_SECONDS = 300;

    private const CACHE_KEY = 'store:taxes:rules';

    /**
     * Basis points in one whole. 21% is 2100.
     */
    private const BP_IN_FULL = 10000;

    /**
     * The rule that applies to a buyer in this country, or null when nothing taxes them.
     *
     * A null country is not an error: a guest whose IP could not be placed still gets the fallback
     * rule, which is the safe answer — under-charging tax is the store's liability, not the
     * buyer's.
     */
    public function resolveFor(?int $countryId): ?StoreTax
    {
        $rules = $this->rules();

        if ($countryId !== null && $rules->has($countryId)) {
            return $rules->get($countryId);
        }

        // Keyed on 0 rather than null: an array key of null is coerced to '' and becomes awkward
        // to reason about after a cache round trip.
        return $rules->get(0);
    }

    /**
     * What a rule takes out of, or adds on top of, a taxable amount.
     *
     * @param  int  $taxableMinor  Minor units, after discounts and before tax
     * @return array{amount: int, total: int, name: ?string, rate_bp: int, is_inclusive: bool}
     */
    public function calculate(int $taxableMinor, ?StoreTax $rule): array
    {
        if (! $rule || $rule->rate_bp <= 0 || $taxableMinor <= 0) {
            return [
                'amount' => 0,
                'total' => max(0, $taxableMinor),
                'name' => null,
                'rate_bp' => 0,
                'is_inclusive' => false,
            ];
        }

        $rate = (int) $rule->rate_bp;

        $amount = $rule->is_inclusive
            // Already inside the price: 21% inclusive of €121 is €21, not €25.41.
            ? (int) round($taxableMinor * $rate / (self::BP_IN_FULL + $rate))
            // Added on top. Floored, so rounding can never charge a buyer more than the rate.
            : intdiv($taxableMinor * $rate, self::BP_IN_FULL);

        return [
            'amount' => $amount,
            // An inclusive tax leaves the total alone — it was always in there.
            'total' => $rule->is_inclusive ? $taxableMinor : $taxableMinor + $amount,
            'name' => $rule->name,
            'rate_bp' => $rate,
            'is_inclusive' => (bool) $rule->is_inclusive,
        ];
    }

    /**
     * Enabled rules keyed by country id, with the fallback under 0.
     *
     * @return Collection<int, StoreTax>
     */
    public function rules(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function () {
            return StoreTax::enabled()->get()->keyBy(fn (StoreTax $tax) => $tax->country_id ?? 0);
        });
    }

    /**
     * Called whenever a rule is written, so an admin sees the change immediately rather than up to
     * five minutes later — and, more importantly, so buyers are never charged at a rate the store
     * has already corrected.
     */
    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}

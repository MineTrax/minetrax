<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_currencies', function (Blueprint $table) {
            $table->id();
            $table->char('code', 3)->unique(); // ISO-4217
            $table->string('name');
            $table->string('symbol');
            $table->string('symbol_position')->default('prefix'); // prefix, suffix

            // ISO-4217 minor unit digits. JPY is 0, most are 2, KWD/BHD/TND are 3. Amounts are
            // always built through brick/money rather than multiplying by 100.
            $table->unsignedTinyInteger('exponent')->default(2);

            $table->decimal('rate_to_base', 20, 10)->default(1);
            $table->timestamp('rate_updated_at')->nullable();

            $table->boolean('is_base')->default(false); // exactly one row, enforced in the request
            $table->boolean('is_enabled')->default(true);

            // Country ISO codes whose visitors default to this currency. An explicit list rather
            // than one derived from `countries.currency`, which stores only a name and a symbol
            // with no ISO-4217 code to match on.
            $table->json('country_codes')->nullable();

            // Applied to converted prices only; explicit per-package overrides bypass rounding.
            $table->string('price_rounding')->default('none'); // none, nearest_whole, nearest_half, charm_99

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('store_package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_package_id')->constrained()->cascadeOnDelete();
            $table->char('currency_code', 3);
            $table->unsignedBigInteger('price'); // minor units in that currency
            $table->timestamps();

            // An explicit override. When absent the price is converted from base and rounded.
            $table->unique(['store_package_id', 'currency_code']);
        });

        // One rule per country, plus at most one country-less rule that catches everyone else.
        //
        // Rows rather than a settings key because a rate is per jurisdiction: EU VAT on digital
        // goods is charged at the buyer's own country's rate, so a store selling into Spain and
        // Germany owes 21% and 19% on otherwise identical orders. A single global percentage
        // cannot express that.
        Schema::create('store_taxes', function (Blueprint $table) {
            $table->id();
            // What the buyer sees on the receipt: "VAT", "GST", "Spain's VAT".
            $table->string('name');
            // Null is the fallback rule, applied to any country with no rule of its own.
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();
            // Basis points, as every other rate in the store. 21% is 2100.
            $table->unsignedInteger('rate_bp')->default(0);
            // Whether the advertised price already contains this tax. The EU, UK and Australia
            // require prices quoted inclusive of it; the US adds it at checkout. A store selling
            // into both needs the answer per rule, not once for the whole shop.
            $table->boolean('is_inclusive')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Two rules for one country would make the rate ambiguous, and an ambiguous tax rate
            // is a liability rather than a bug.
            $table->unique('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_taxes');
        Schema::dropIfExists('store_package_prices');
        Schema::dropIfExists('store_currencies');
    }
};

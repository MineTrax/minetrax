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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_package_prices');
        Schema::dropIfExists('store_currencies');
    }
};

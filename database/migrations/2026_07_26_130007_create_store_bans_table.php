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
        // At least one identity column must be set. Any match blocks checkout, so a chargeback
        // can auto-ban by player uuid while a manual ban can target an email or IP.
        Schema::create('store_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->char('player_uuid', 36)->nullable()->index();
            $table->string('ip_address')->nullable()->index();
            $table->string('email')->nullable()->index();

            $table->string('reason')->nullable();
            $table->boolean('is_automatic')->default(false); // raised by a chargeback
            $table->timestamp('expires_at')->nullable();     // null = permanent

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_bans');
    }
};

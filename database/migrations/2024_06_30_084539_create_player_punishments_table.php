<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('player_punishments', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('plugin_punishment_id')->nullable();
            $table->string('plugin_name')->nullable();
            $table->char('uuid', 36)->nullable()->index(); // uuid
            $table->string('ip_address')->nullable();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->boolean('is_ipban')->default(false);
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable()->index();

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->json('insights')->nullable(); // AI insights
            $table->string('server_scope')->nullable();
            $table->string('origin_server_name')->nullable();
            $table->foreignId('scope_server_id')->nullable()->constrained('servers')->nullOnDelete();
            $table->foreignId('origin_server_id')->nullable()->constrained('servers')->nullOnDelete();

            $table->char('creator_uuid', 36)->nullable()->index(); // uuid
            $table->string('creator_username')->nullable();
            $table->char('remover_uuid', 36)->nullable()->index(); // uuid
            $table->string('remover_username')->nullable();
            $table->string('removed_reason')->nullable();
            $table->timestamp('removed_at')->nullable();


            // created_by, updated_by, removed_by are only set if action done from web.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->index(['type', 'plugin_punishment_id', 'plugin_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_punishments');
    }
};

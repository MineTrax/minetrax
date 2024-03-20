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
        Schema::create('recruitment_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('recruitment_id')->constrained('recruitments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, inprogress, approved, rejected, withdrawn, onhold

            $table->foreignId('last_act_by')->nullable()->constrained('users')->nullOnDelete(); // Last status change by.
            $table->timestamp('last_act_at')->nullable();
            $table->text('last_act_reason')->nullable(); // Reason for last status change.
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_submissions');
    }
};

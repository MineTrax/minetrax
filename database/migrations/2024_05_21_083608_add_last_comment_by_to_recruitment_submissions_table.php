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
        Schema::table('recruitment_submissions', function (Blueprint $table) {
            $table->foreignId('last_comment_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_comment_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_submissions', function (Blueprint $table) {
            $table->dropColumn('last_comment_by');
            $table->dropColumn('last_comment_at');
        });
    }
};

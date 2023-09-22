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
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_external')->default(false); // if external, then file_url is set.
            $table->boolean('is_external_url_hidden')->default(false); // external only setting, if true minetrax download file and stream to user so url is not exposed.
            $table->string('file_url')->nullable(); // if external, then file_path is url.
            $table->string('file_name')->nullable();

            $table->boolean('is_only_auth')->default(false); // only authenticated user.
            $table->integer('min_role_weight_required')->nullable(); // if authenticated, min weight of role of user needed.

            $table->boolean('is_active')->default(true);
            $table->bigInteger('download_count')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};

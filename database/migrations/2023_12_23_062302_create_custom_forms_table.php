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
        Schema::create('custom_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('status')->default('active'); // draft, active, disabled, archived

            $table->string('can_create_submission')->default('anyone'); // anyone -> anyone, "auth" -> only authenticated users, "staff" -> only staff
            $table->boolean('require_restricted_permission_to_view_submission')->default(false); // Only staff with view restricted_custom_form_submission permission can view submission for this form.
            $table->boolean('is_notify_staff_on_submission')->default(false); // notify staff (with view access) when new submission is made.

            $table->json('fields')->nullable();

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
        Schema::dropIfExists('custom_forms');
    }
};

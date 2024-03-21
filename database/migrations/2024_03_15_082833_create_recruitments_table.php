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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->string('status')->default('active'); // draft, active, disabled, archived

            $table->integer('max_submission_per_user')->nullable(); // null -> unlimited
            $table->integer('submission_cooldown_in_seconds')->nullable(); // null -> no cooldown
            $table->boolean('is_allow_only_player_linked_users')->default(false);
            $table->boolean('is_allow_only_verified_users')->default(false);
            $table->boolean('is_allow_messages_from_users')->default(true);

            $table->integer('min_role_weight_to_view_submission')->nullable(); // Only staff with role weight >= min_role_weight_to_view_submission can view submission for this.
            $table->integer('min_role_weight_to_vote_on_submission')->nullable();
            $table->integer('min_role_weight_to_act_on_submission')->nullable(); // Only staff with role weight >= min_role_weight_to_act_on_submission can act on submission for this. Accept, Reject, etc.
            $table->boolean('is_notify_staff_on_submission')->default(true);

            $table->foreignId('related_role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->json('post_approve_actions')->nullable(); // Actions to be performed after submission is approved. (e.g. Assign role, Send message, etc.)

            $table->json('fields')->nullable();

            $table->integer('min_role_weight_to_create_submission')->nullable(); // Not Implemented, For future use
            $table->boolean('is_allow_only_staff_members')->default(false);         // Not Implemented, For future use

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
        Schema::dropIfExists('recruitments');
    }
};

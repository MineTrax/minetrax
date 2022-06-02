<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('username', 50)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['m', 'f', 'o'])->nullable();
            $table->json('social_links')->nullable(); // discord, steam, twitter, youtube, facebook, twitch and website
            $table->text('about')->nullable();
            $table->rememberToken();
            //$table->foreignId('current_team_id')->nullable();
            $table->text('profile_photo_path')->nullable();
            $table->text('cover_image_path')->nullable();

            $table->timestamp('verified_at')->nullable();        // Used for verified badge
            $table->timestamp('banned_at')->nullable();
            $table->timestamp('muted_at')->nullable();

            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');

            $table->json('settings')->nullable();

            $table->smallInteger('user_setup_status')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

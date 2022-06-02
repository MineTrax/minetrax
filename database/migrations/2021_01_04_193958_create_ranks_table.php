<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shortname')->unique();
            $table->unsignedInteger('total_score_needed')->default(0);
            $table->unsignedBigInteger('total_play_one_minute_needed')->default(0);
            $table->unsignedInteger('rating_needed')->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('weight');         // normalize(total_score_needed + total_play_one_minute_needed). Its to sort rank list.

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('ranks');
    }
}

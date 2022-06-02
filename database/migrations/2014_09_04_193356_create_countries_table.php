<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('iso_code')->nullable()->unique();
            $table->string('currency')->nullable();
            $table->json('languages')->nullable();
            $table->string('region')->nullable();
            $table->string('flag')->nullable();
            $table->boolean('is_un_member')->nullable();
            $table->boolean('is_independent')->nullable();
            $table->string('status')->nullable();
            $table->string('latlng')->nullable();
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
        Schema::dropIfExists('countries');
    }
}

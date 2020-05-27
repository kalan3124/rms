<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itinerary', function (Blueprint $table) {
            $table->increments('i_id');
            
            $table->tinyInteger('i_year');
            $table->tinyInteger('i_month');

            $table->unsignedInteger('rep_id')->nullable();
            $table->foreign('rep_id')->references('id')->on('users');
            
            $table->unsignedInteger('fm_id')->nullable();
            $table->foreign('fm_id')->references('id')->on('users');

            $table->softDeletes();
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
        Schema::dropIfExists('itinerary');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItineraryDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itinerary_day_type', function (Blueprint $table) {
            $table->increments('idt_id');

            $table->unsignedInteger('i_id')->nullable();
            $table->foreign('i_id')->references('i_id')->on('itinerary');

            $table->unsignedInteger('dt_id')->nullable();
            $table->foreign('dt_id')->references('dt_id')->on('day_type');
            
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
        Schema::dropIfExists('itinerary_day_type');
    }
}

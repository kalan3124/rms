<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStandardItineraryDateDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_itinerary_date_day_type', function (Blueprint $table) {
            $table->increments('siddt_id');

            $table->unsignedInteger('sid_id')->nullable();
            $table->foreign('sid_id')->references('sid_id')->on('standard_itinerary_date');

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
        Schema::dropIfExists('standard_itinerary_date_day_type');
    }
}

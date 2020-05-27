<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSdcItineraryDateHasDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdc_itinerary_date_has_day_type', function (Blueprint $table) {
            $table->increments('sdc_iddt_id');

            $table->unsignedInteger('sdc_id_id')->nullable();
            $table->foreign('sdc_id_id')->references('sdc_id_id')->on('sdc_itinerary_date');

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
        Schema::dropIfExists('sdc_itinerary_date_has_day_type');
    }
}

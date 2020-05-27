<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItineraryDateChangeAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itinerary_date_change_areas', function (Blueprint $table) {
            $table->increments('idca_id');
            $table->unsignedInteger('idc_id')->nullable();
            $table->foreign('idc_id')->references('idc_id')->on('itinerary_date_changes');
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
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
        Schema::dropIfExists('itinerary_date_change_areas');
    }
}

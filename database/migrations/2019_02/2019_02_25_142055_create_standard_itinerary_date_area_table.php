<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStandardItineraryDateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_itinerary_date_area', function (Blueprint $table) {
            $table->increments('sida_id');
            
            $table->unsignedInteger('sid_id')->nullable();
            $table->foreign('sid_id')->references('sid_id')->on('standard_itinerary_date');

            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');

            $table->unsignedInteger('ar_id')->nullable();
            $table->foreign('ar_id')->references('ar_id')->on('area');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('dis_id')->on('district');

            $table->unsignedInteger('pv_id')->nullable();
            $table->foreign('pv_id')->references('pv_id')->on('province');

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
        Schema::dropIfExists('standard_itinerary_date_area');
    }
}

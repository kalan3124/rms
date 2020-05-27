<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaItineraryDateHasDayType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_itinerary_date_has_day_type', function (Blueprint $table) {
            $table->increments('s_iddt_id');
            
            $table->unsignedInteger('s_id_id')->nullable();
            $table->foreign('s_id_id')->references('s_id_id')->on('sfa_itinerary_date');
            
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
        Schema::dropIfExists('sfa_itinerary_date_has_day_type');
    }
}

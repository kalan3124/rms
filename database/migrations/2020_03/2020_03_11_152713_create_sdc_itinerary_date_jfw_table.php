<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSdcItineraryDateJfwTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdc_itinerary_date_jfw', function (Blueprint $table) {
            $table->increments('sdc_idj_id');

            $table->unsignedInteger('sdc_id_id')->nullable();
            $table->foreign('sdc_id_id')->references('sdc_id_id')->on('sdc_itinerary_date');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->time('sdc_idj_from');
            $table->time('sdc_idj_to');

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
        Schema::dropIfExists('sdc_itinerary_date_jfw');
    }
}

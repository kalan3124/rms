<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSdcItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdc_itinerary', function (Blueprint $table) {
            $table->increments('sdc_i_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('sdc_i_year');
            $table->tinyInteger('sdc_i_month');

            $table->timestamp('sdc_i_aprvd_at')->nullable();
            $table->unsignedInteger('sdc_aprvd_u_id')->nullable();
            $table->foreign('sdc_aprvd_u_id')->references('id')->on('users');

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
        Schema::dropIfExists('sdc_itinerary');
    }
}

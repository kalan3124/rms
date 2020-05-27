<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStandardItineraryDateCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_itinerary_date_customer', function (Blueprint $table) {
            $table->increments('sidc_id');

            $table->unsignedInteger('sid_id')->nullable();
            $table->foreign('sid_id')->references('sid_id')->on('standard_itinerary_date');

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('hos_stf_id')->nullable();
            $table->foreign('hos_stf_id')->references('hos_stf_id')->on('other_hospital_staff');

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
        Schema::dropIfExists('standard_itinerary_date_customer');
    }
}

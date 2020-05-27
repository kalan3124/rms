<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStandardItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standard_itinerary_date', function (Blueprint $table) {
            $table->increments('sid_id');
            $table->tinyInteger('sid_date');
            $table->unsignedInteger('si_id')->nullable();
            $table->foreign('si_id')->references('si_id')->on('standard_itinerary');
            $table->decimal('sid_bata',10,2)->default(0);
            $table->decimal('sid_mileage',10,2)->default(0);
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
        Schema::dropIfExists('standard_itinerary_date');
    }
}

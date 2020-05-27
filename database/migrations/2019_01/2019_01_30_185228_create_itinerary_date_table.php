<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itinerary_date', function (Blueprint $table) {
            $table->increments('id_id');
            $table->tinyInteger('id_date');
            $table->unsignedInteger('i_id')->nullable();
            $table->foreign('i_id')->references('i_id')->on('itinerary');
            $table->decimal('i_bata',10,2)->default(0);
            $table->decimal('i_mileage',10,2)->default(0);
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
        Schema::dropIfExists('itinerary_date');
    }
}

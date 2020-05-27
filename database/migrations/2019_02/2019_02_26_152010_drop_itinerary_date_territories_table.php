<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropItineraryDateTerritoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('itinerary_territory');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('itinerary_territory', function (Blueprint $table) {
            $table->increments('it_id');

            $table->unsignedInteger('id_id')->nullable();
            $table->foreign('id_id')->references('id_id')->on('itinerary_date');
            
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
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_itinerary_date', function (Blueprint $table) {
            $table->increments('s_id_id');

            $table->tinyInteger('s_id_date');

            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');

            $table->decimal('s_id_mileage',12,2)->nullable();

            $table->unsignedInteger('s_i_id')->nullable();
            $table->foreign('s_i_id')->references('s_i_id')->on('sfa_itinerary');

            $table->unsignedInteger('route_id')->nullable();
            $table->foreign('route_id')->references('route_id')->on('sfa_route');

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
        Schema::dropIfExists('sfa_itinerary_date');
    }
}

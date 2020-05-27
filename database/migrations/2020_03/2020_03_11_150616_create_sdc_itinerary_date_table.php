<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSdcItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sdc_itinerary_date', function (Blueprint $table) {
            $table->increments('sdc_id_id');

            $table->tinyInteger('sdc_id_date');

            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');

            $table->decimal('sdc_id_mileage',12,2)->nullable();

            $table->unsignedInteger('sdc_i_id')->nullable();
            $table->foreign('sdc_i_id')->references('sdc_i_id')->on('sdc_itinerary');

            $table->unsignedInteger('route_id')->nullable();
            $table->foreign('route_id')->references('route_id')->on('sfa_route');

            $table->tinyInteger('sdc_id_type')->default(0);

            $table->decimal('day_target',15,2)->nullable();

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
        Schema::dropIfExists('sdc_itinerary_date');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSrItineraryDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_sr_itinerary_details', function (Blueprint $table) {
            $table->increments('sr_i_id');

            $table->tinyInteger('sr_i_year')->nullable();
            $table->tinyInteger('sr_i_month')->nullable();
            $table->tinyInteger('sr_i_date')->nullable();

            $table->unsignedInteger('sr_id')->nullable();
            $table->foreign('sr_id')->references('id')->on('users');

            $table->unsignedInteger('route_id')->nullable();
            $table->foreign('route_id')->references('route_id')->on('sfa_route');

            $table->integer('outlet_count')->nullable();
            $table->decimal('sr_mileage', 10, 2);

            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');

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
        Schema::dropIfExists('sfa_sr_itinerary_details');
    }
}

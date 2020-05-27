<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdditionalRoutePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_route_plans', function (Blueprint $table) {
            $table->increments('arp_id');
            $table->unsignedInteger('id_id')->nullable();
            $table->foreign('id_id')->references('id_id')->on('itinerary_date');
            $table->string('arp_description');
            $table->decimal('arp_mileage',10,2);
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
        Schema::dropIfExists('additional_route_plans');
    }
}

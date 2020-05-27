<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleTypeRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_type_rates', function (Blueprint $table) {
            $table->increments('vhtr_id');
            $table->decimal('vhtr_rate',10,2);
            $table->unsignedInteger('u_tp_id')->nullable();
            $table->foreign('u_tp_id')->references('u_tp_id')->on('user_types');
            $table->unsignedInteger('vht_id')->nullable();
            $table->foreign('vht_id')->references('vht_id')->on('vehicle_types');
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
        Schema::dropIfExists('vehicle_type_rates');
    }
}

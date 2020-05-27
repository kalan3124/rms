<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentVehicleTypeRateToStationMileageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('station_mileage', function (Blueprint $table) {
            $table->decimal('vhtr_rate',10,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('station_mileage', function (Blueprint $table) {
            $table->dropColumn('vhtr_rate');
        });
    }
}

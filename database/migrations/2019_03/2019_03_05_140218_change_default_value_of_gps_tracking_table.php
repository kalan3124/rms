<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDefaultValueOfGpsTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gps_tracking',function (Blueprint $table){
            $table->dateTime('gt_time')->default('0000-00-00 00:00:00')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gps_tracking',function (Blueprint $table){
            $table->timestamp('gt_time')->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGpsTrackingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gps_tracking', function (Blueprint $table) {
            $table->increments('gt_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->decimal('gt_lon', 10, 7);
            $table->decimal('gt_lat', 10, 7);

            $table->tinyInteger('gt_btry');
            $table->decimal('gt_speed',10,8);

            $table->timestamp('gt_time');

            $table->decimal('gt_brng',10,4);

            $table->decimal('gt_accu',10,4);

            $table->tinyInteger('gt_prvdr')->comment('0-GPS,1-Network,2-Undefind');
            
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
        Schema::dropIfExists('gps_tracking');
    }
}

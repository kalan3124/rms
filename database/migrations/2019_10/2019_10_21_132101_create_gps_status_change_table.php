<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGpsStatusChangeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gps_status_change', function (Blueprint $table) {
            $table->increments('gsc_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->decimal('gsc_lon', 10, 7);
            $table->decimal('gsc_lat', 10, 7);

            $table->tinyInteger('gsc_btry');
            $table->decimal('gsc_speed',10,8);

            $table->timestamp('gsc_time')->default('0000-00-00 00:00:00');

            $table->decimal('gsc_brng',10,4);

            $table->decimal('gsc_accu',10,4);

            $table->tinyInteger('gsc_prvdr')->comment('0-GPS,1-Network,2-Undefind');

            $table->tinyInteger('gsc_status')->comment("0-Activate,1-Deactivate");

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
        Schema::dropIfExists('gps_status_change');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_attendance', function (Blueprint $table) {
            $table->increments('att_id');

            $table->unsignedInteger('u_id');
            $table->foreign('u_id')->references('id')->on('users');

            $table->decimal('check_in_lat', 20,15);
            $table->decimal('check_in_lon', 20,15);
            $table->timestamp('check_in_time')->nullable();
            $table->integer('check_in_mileage');
            $table->float('check_in_battery', 8, 2);
            $table->integer('check_in_loc_type')->nullable()->comments('0-GPS,1-Network,2-Undefind');

            $table->decimal('check_out_lat', 20,15);
            $table->decimal('check_out_lon', 20,15);
            $table->timestamp('check_out_time')->nullable();
            $table->integer('check_out_mileage');
            $table->float('check_out_battery', 8, 2);
            $table->integer('check_out_loc_type')->nullable()->comments('0-GPS,1-Network,2-Undefind');

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
        Schema::dropIfExists('user_attendance');
    }
}

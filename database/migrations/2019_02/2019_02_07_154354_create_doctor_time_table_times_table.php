<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorTimeTableTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_time_table_times', function (Blueprint $table) {
            $table->increments('dttt_id');

            $table->integer('dttt_week_day')->comment("0=Sunday,1=Monday...");

            $table->unsignedInteger('ins_id')->nullable();
            $table->foreign('ins_id')->references('ins_id')->on('institutions');

            $table->unsignedInteger('dtt_id')->nullable();
            $table->foreign('dtt_id')->references('dtt_id')->on('doctor_time_table');

            $table->time('dttt_s_time');
            $table->time('dttt_e_time');

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
        Schema::dropIfExists('doctor_time_table_times');
    }
}

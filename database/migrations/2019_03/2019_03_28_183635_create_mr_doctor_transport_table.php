<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrDoctorTransportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mr_doctor_transport', function (Blueprint $table) {
            $table->increments('mr_dt_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            
            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('bata_rsn_id')->nullable()->comment('bata');
            $table->foreign('bata_rsn_id')->references('rsn_id')->on('reason');

            $table->unsignedInteger('exp_rsn_id')->nullable()->comment('expenses');
            $table->foreign('exp_rsn_id')->references('rsn_id')->on('reason');

            $table->decimal('start_mileage',10,2)->default(0);
            $table->decimal('end_mileage',10,2)->default(0);

            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lon', 10, 7)->nullable();

            $table->tinyInteger('start_loc_type')->default(2)->comment('0-gps, 1-network, 2-undefind');
            $table->tinyInteger('end_loc_type')->default(2)->comment('0-gps, 1-network, 2-undefind');

            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lon', 10, 7)->nullable();

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

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
        Schema::dropIfExists('mr_doctor_transport');
    }
}

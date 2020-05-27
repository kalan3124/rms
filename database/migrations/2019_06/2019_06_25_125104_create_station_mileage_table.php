<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationMileageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_mileage', function (Blueprint $table) {
            $table->increments('stm_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->decimal('exp_amount',10,2)->nullable();
            $table->string('exp_remark')->nullable();
            $table->timestamp('stm_date')->nullable();
            $table->string('app_version')->nullable();
            $table->date('exp_date')->nullable()->comment('backdate');

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
        Schema::dropIfExists('station_mileage');
    }
}

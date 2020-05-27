<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorSrCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_sr_customer', function (Blueprint $table) {
            $table->increments('dsc_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->unsignedInteger('dc_id')->nullable();
            $table->foreign('dc_id')->references('dc_id')->on('distributor_customer');

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
        Schema::dropIfExists('distributor_sr_customer');
    }
}

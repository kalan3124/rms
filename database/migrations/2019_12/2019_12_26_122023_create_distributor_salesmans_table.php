<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorSalesmansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_salesmans', function (Blueprint $table) {
            $table->increments('dis_salesman_id');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->unsignedInteger('sr_id')->comment('salesman')->nullable();
            $table->foreign('sr_id')->references('id')->on('users');

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
        Schema::dropIfExists('distributor_salesmans');
    }
}

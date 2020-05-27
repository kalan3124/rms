<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDistributorReturnItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_return_item', function (Blueprint $table) {
            $table->increments('dri_id');

            $table->unsignedInteger('rsn_id')->nullable();
            $table->foreign('rsn_id')->references('rsn_id')->on('reason');

            $table->unsignedInteger('dis_return_id')->nullable();
            $table->foreign('dis_return_id')->references('dis_return_id')->on('distributor_return');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

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
        Schema::dropIfExists('distributor_return_item');
    }
}

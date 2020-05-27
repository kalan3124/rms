<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorReturnBonusItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_return_bonus_item', function (Blueprint $table) {
            $table->increments('drbi_id');

            $table->unsignedInteger('dis_return_id')->nullable();
            $table->foreign('dis_return_id')->references('dis_return_id')->on('distributor_return');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');

            $table->integer('drbi_qty');

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
        Schema::dropIfExists('distributor_return_bonus_item');
    }
}

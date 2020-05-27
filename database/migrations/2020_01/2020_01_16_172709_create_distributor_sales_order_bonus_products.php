<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorSalesOrderBonusProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_sales_order_bonus_products', function (Blueprint $table) {
            $table->increments('dsobp_id');

            $table->unsignedInteger('dist_order_id')->nullable();
            $table->foreign('dist_order_id')->references('dist_order_id')->on('distributor_sales_order');

            $table->integer('dsobp_qty');

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');

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
        Schema::dropIfExists('distributor_sales_order_bonus_products');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockAdjusmentProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjusment_product', function (Blueprint $table) {
            $table->increments('stk_adj_pro_id');

            $table->unsignedInteger('stk_adj_id')->comment('adjusment id')->nullable();
            $table->foreign('stk_adj_id')->references('stk_adj_id')->on('stock_adjusment');

            $table->unsignedInteger('product_id')->comment('product')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('db_id')->comment('batch')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

            $table->integer('stk_adj_qty')->nullable();

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
        Schema::dropIfExists('stock_adjusment_product');
    }
}

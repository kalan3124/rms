<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorSalesOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_sales_order_products', function (Blueprint $table) {
            $table->increments('dist_order_pro_id');

            $table->unsignedInteger('dist_order_id')->nullable();
            $table->foreign('dist_order_id')->references('dist_order_id')->on('distributor_sales_order');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->integer('sales_qty')->nullable();
            $table->decimal('price',10,2)->nullable();

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
        Schema::dropIfExists('distributor_sales_order_products');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_stock', function (Blueprint $table) {
            $table->increments('ds_id');
            
            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('db_id')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

            $table->integer('ds_credit_qty')->default(0);

            $table->integer('ds_debit_qty')->default(0);

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
        Schema::dropIfExists('distributor_stock');
    }
}

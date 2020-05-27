<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWriteOffProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('write_off_product', function (Blueprint $table) {
            $table->increments('wo_pro_id');

            $table->unsignedInteger('wo_id')->comment('write off id')->nullable();
            $table->foreign('wo_id')->references('wo_id')->on('write_off');

            $table->unsignedInteger('product_id')->comment('product')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('db_id')->comment('batch')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

            $table->integer('wo_qty')->nullable();

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
        Schema::dropIfExists('write_off_product');
    }
}

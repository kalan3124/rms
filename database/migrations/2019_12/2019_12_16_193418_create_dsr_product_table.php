<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsrProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dsr_product', function (Blueprint $table) {
            $table->increments('dsrp_id');

            $table->unsignedInteger('dsr_id')->nullable();
            $table->foreign('dsr_id')->references('id')->on('users');

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
        Schema::dropIfExists('dsr_product');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockAdjusmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjusment', function (Blueprint $table) {
            $table->increments('stk_adj_id');

            $table->string('stk_adj_no')->nullable();

            $table->unsignedInteger('dis_id')->comment('distributor')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->dateTime('stk_adj_date');

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
        Schema::dropIfExists('stock_adjusment');
    }
}

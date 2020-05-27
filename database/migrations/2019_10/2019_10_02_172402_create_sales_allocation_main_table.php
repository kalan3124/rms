<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesAllocationMainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_allocation_main', function (Blueprint $table) {
            $table->increments('sam_id');

            $table->unsignedInteger('tm_id')->nullable();
            $table->foreign('tm_id')->references('tm_id')->on('teams');

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
        Schema::dropIfExists('sales_allocation_main');
    }
}

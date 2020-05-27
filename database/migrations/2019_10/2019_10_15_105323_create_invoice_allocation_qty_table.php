<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceAllocationQtyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_allocation_qty', function (Blueprint $table) {
            $table->increments('iaq_id');

            $table->unsignedInteger('ia_id')->nullable();
            $table->foreign('ia_id')->references('ia_id')->on('invoice_allocations');

            $table->unsignedInteger('tm_id')->nullable();
            $table->foreign('tm_id')->references('tm_id')->on('teams');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('iaq_qty');
 
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
        Schema::dropIfExists('invoice_allocation_qty');
    }
}

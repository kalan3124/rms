<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmpInvoiceAllocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_invoice_allocation', function (Blueprint $table) {
            $table->increments('tia_id');

            $table->unsignedInteger('ia_id')->nullable();
            $table->foreign('ia_id')->references('ia_id')->on('invoice_allocations');

            $table->unsignedInteger('inv_line_id')->nullable();
            $table->foreign('inv_line_id')->references('inv_line_id')->on('invoice_line');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('tia_qty');

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
        Schema::dropIfExists('tmp_invoice_allocation');
    }
}

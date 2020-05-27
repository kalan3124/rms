<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_allocations', function (Blueprint $table) {
            $table->increments('ia_id');

            $table->unsignedInteger('tm_id')->nullable();
            $table->foreign('tm_id')->references('tm_id')->on('teams');

            $table->unsignedInteger('inv_line_id')->nullable();
            $table->foreign('inv_line_id')->references('inv_line_id')->on('invoice_line');

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
        Schema::dropIfExists('invoice_allocations');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->increments('inv_head_id');
            $table->string('company')->nullable();
            $table->string('customer_no')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('invoice_series')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('site')->nullable();
            $table->string('currency')->nullable();
            $table->string('order_no')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->decimal('gross_amount',10,2)->nullable();
            $table->string('customer_po_no')->nullable();
            $table->timestamp('last_updated_on')->nullable(); 

            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

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
        Schema::dropIfExists('invoice');
    }
}

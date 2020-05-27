<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorPaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_payment_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('distributor_payment_id')->nullable();
            $table->foreign('distributor_payment_id')->references('id')->on('distributor_payments');
            $table->unsignedInteger('di_id')->nullable();
            $table->foreign('di_id')->references('di_id')->on('distributor_invoice');
            $table->decimal('amount',10,2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('distributor_payment_invoices');
    }
}

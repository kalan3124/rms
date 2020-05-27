<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorInvoiceBonusLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_invoice_bonus_line', function (Blueprint $table) {
            $table->increments('dibl_id');

            $table->unsignedInteger('di_id')->nullable();
            $table->foreign('di_id')->references('di_id')->on('distributor_invoice');

            $table->integer('dibl_qty');
            $table->decimal('dibl_unit_price',12,2);

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');

            $table->unsignedInteger('db_id')->nullable();
            $table->foreign('db_id')->references('db_id')->on('distributor_batches');

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
        Schema::dropIfExists('distributor_invoice_bonus_line');
    }
}

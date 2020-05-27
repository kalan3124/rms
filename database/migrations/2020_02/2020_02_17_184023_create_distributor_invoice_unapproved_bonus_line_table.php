<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorInvoiceUnapprovedBonusLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_invoice_unapproved_bonus_line', function (Blueprint $table) {
            $table->increments('diubl_id');

            $table->unsignedInteger('di_id')->nullable();
            $table->foreign('di_id')->references('di_id')->on('distributor_invoice');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');

            $table->integer('diubl_qty');

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
        Schema::dropIfExists('distributor_invoice_unapproved_bonus_line');
    }
}

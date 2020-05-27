<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistributorInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('distributor_invoice', function (Blueprint $table) {
            $table->increments('di_id');

            $table->unsignedInteger('dist_order_id')->nullable();
            $table->foreign('dist_order_id')->references('dist_order_id')->on('distributor_sales_order');

            $table->decimal('di_amount',10,2);

            $table->decimal('di_discount');

            $table->unsignedInteger('dsr_id')->nullable();
            $table->foreign('dsr_id')->references('id')->on('users');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

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
        Schema::dropIfExists('distributor_invoice');
    }
}

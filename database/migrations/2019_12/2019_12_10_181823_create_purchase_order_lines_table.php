<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->increments('pol_id');

            $table->unsignedInteger('po_id')->nullable();
            $table->foreign('po_id')->references('po_id')->on('purchase_order');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->integer('pol_qty');
            $table->decimal('pol_price',10,2)->nullable();
            $table->decimal('pol_amount',10,2)->nullable();
            
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
        Schema::dropIfExists('purchase_order_lines');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaTargetProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_target_products', function (Blueprint $table) {
            $table->increments('sfa_tp_id');
            $table->unsignedInteger('sfa_trg_id')->nullable();
            $table->foreign('sfa_trg_id')->references('sfa_trg_id')->on('sfa_target');
            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');
            $table->integer('stp_qty');
            $table->decimal('budget_price',15,2);
            $table->decimal('stp_amount',15,2);
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
        Schema::dropIfExists('sfa_target_products');
    }
}

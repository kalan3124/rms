<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesPriceListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_price_list', function (Blueprint $table) {
            $table->increments('spl_id');
            $table->string('price_list_no')->nullable();
            $table->string('description')->nullable();
            $table->string('sales_price_group_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('catalog_no')->nullable();

            $table->integer('min_quantity')->nullable();
            $table->timestamp('valid_from_date')->nullable();
            $table->string('base_price_site')->nullable();
            $table->decimal('base_price',10,2)->nullable();

            $table->decimal('base_price_incl_tax',10,2)->nullable();
            $table->decimal('percentage_offset',10,2)->nullable();
            $table->decimal('amount_offset',10,2)->nullable();

            $table->decimal('sales_price',10,2)->nullable();
            $table->decimal('sales_prices_incl_tax',10,2)->nullable();
            $table->timestamp('last_updated_on')->nullable();

            $table->decimal('discount')->nullable();
            $table->string('discount_type')->nullable();
            $table->integer('price_break_template_id')->nullable();
            $table->string('sales_price_type')->nullable();
            $table->string('state')->nullable();

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
        Schema::dropIfExists('sales_price_list');
    }
}

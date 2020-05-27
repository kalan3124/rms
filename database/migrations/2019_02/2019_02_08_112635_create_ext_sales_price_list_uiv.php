<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExtSalesPriceListUiv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ext_sales_price_list_uiv', function (Blueprint $table) {
            $table->increments('s_price_id');
            $table->string('price_list_no')->nullable();
            $table->string('description')->nullable();
            $table->string('sales_price_group_id')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('catalog_no')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->timestamp('valid_from_date')->nullable();
            $table->string('base_price_site')->nullable();
            $table->double('base_price',8,2)->nullable();

            $table->double('base_price_incl_tax',8,2)->nullable();
            $table->double('percentage_offset',8,2)->nullable();
            $table->double('amount_offset',8,2)->nullable();

            $table->double('sales_price',8,2)->nullable();
            $table->double('sales_prices_incl_tax',8,2)->nullable();
            $table->timestamp('last_updated_on')->nullable();
            $table->double('discount',8,2)->nullable();

            $table->string('discount_type')->nullable();
            $table->integer('price_break_template_id')->nullable();
            $table->string('sales_price_type')->nullable();
            $table->string('state')->nullable();

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
        Schema::dropIfExists('ext_sales_price_list_uiv');
    }
}

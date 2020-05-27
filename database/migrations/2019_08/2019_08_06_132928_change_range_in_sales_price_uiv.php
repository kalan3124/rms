<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeRangeInSalesPriceUiv extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ext_sales_price_list_uiv', function (Blueprint $table) {
            
            $table->decimal('base_price',12,2)->nullable()->change();
            $table->decimal('base_price_incl_tax',12,2)->nullable()->change();
            $table->decimal('percentage_offset',12,2)->nullable()->change();
            $table->decimal('amount_offset',12,2)->nullable()->change();
            $table->decimal('sales_price',12,2)->nullable()->change();
            $table->decimal('sales_prices_incl_tax',12,2)->nullable()->change();
            $table->decimal('discount',12,2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ext_sales_price_list_uiv', function (Blueprint $table) {
            $table->decimal('base_price',8,2)->nullable()->change();
            $table->decimal('base_price_incl_tax',8,2)->nullable()->change();
            $table->decimal('percentage_offset',8,2)->nullable()->change();
            $table->decimal('amount_offset',8,2)->nullable()->change();
            $table->decimal('sales_price',8,2)->nullable()->change();
            $table->decimal('sales_prices_incl_tax',8,2)->nullable()->change();
            $table->decimal('discount',8,2)->nullable()->change();

        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeInSalesPriceListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_price_list', function (Blueprint $table) {
            $table->decimal('base_price', 12,2)->nullable()->change();
            $table->decimal('base_price_incl_tax', 12,2)->nullable()->change();
            $table->decimal('percentage_offset', 12,2)->nullable()->change();
            $table->decimal('amount_offset', 12,2)->nullable()->change();
            $table->decimal('sales_price', 12,2)->nullable()->change();
            $table->decimal('sales_prices_incl_tax', 12,2)->nullable()->change();
            $table->decimal('discount', 12,2)->nullable()->change();

            $table->dropColumn('valid_from_date');
            $table->dropColumn('last_updated_on');
        });

        Schema::table('sales_price_list', function (Blueprint $table) {
            $table->dateTime('valid_from_date')->nullable()->after('min_quantity');
            $table->dateTime('last_updated_on')->nullable()->after('sales_prices_incl_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_price_list', function (Blueprint $table) {
            $table->decimal('base_price',8,2)->nullable()->change();
            $table->decimal('base_price_incl_tax',8,2)->nullable()->change();
            $table->decimal('percentage_offset',8,2)->nullable()->change();
            $table->decimal('amount_offset',8,2)->nullable()->change();
            $table->decimal('sales_price',8,2)->nullable()->change();
            $table->decimal('sales_prices_incl_tax',8,2)->nullable()->change();
            $table->decimal('discount',8,2)->nullable()->change();

            $table->dropColumn('valid_from_date');
            $table->dropColumn('last_updated_on');
        });

        Schema::table('sales_price_list', function (Blueprint $table) {
            $table->timestamp('valid_from_date')->nullable()->after('min_quantity');
            $table->timestamp('last_updated_on')->nullable()->after('sales_prices_incl_tax');
        });
    }
}

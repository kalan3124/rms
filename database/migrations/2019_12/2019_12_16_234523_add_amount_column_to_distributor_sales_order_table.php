<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAmountColumnToDistributorSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_sales_order', function (Blueprint $table) {
            $table->decimal('sales_order_amt',10,2);

            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_sales_order', function (Blueprint $table) {
            $table->dropColumn('sales_order_amt');

            $table->dropForeign(['sub_twn_id']);
            $table->dropColumn('sub_twn_id');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReModifyTableSfaSaleOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_sales_order', function (Blueprint $table) {
            $table->decimal('sales_order_amt',15,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sfa_sales_order', function (Blueprint $table) {
            $table->dropColumn('sales_order_amt');
        });
    }
}

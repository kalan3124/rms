<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTableDistributorSalesOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_sales_order', function (Blueprint $table) {
            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');
            $table->string('order_no')->unique()->change();
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
            $table->dropForeign(['dis_id']);
            $table->dropColumn('dis_id');
            $table->dropUnique('order_no');
        });
    }
}

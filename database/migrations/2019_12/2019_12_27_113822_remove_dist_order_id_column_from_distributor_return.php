<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDistOrderIdColumnFromDistributorReturn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->dropForeign(['dist_order_id']);
            $table->dropColumn('dist_order_id');
            $table->string('dist_return_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->unsignedInteger('dist_order_id')->nullable();
            $table->foreign('dist_order_id')->references('dist_order_id')->on('distributor_sales_order');
            $table->dropColumn('dist_return_number');
        });
    }
}

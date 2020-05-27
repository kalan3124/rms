<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModfiyTableDistributorCustomer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {
            $table->unsignedInteger('price_group')->nullable();
            $table->foreign('price_group')->references('spl_id')->on('sales_price_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {
            $table->dropForeign(['price_group']);
            $table->dropColumn('price_group');
        });
    }
}

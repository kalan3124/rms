<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClassIdToDistributorCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {


            $table->unsignedInteger('dcc_id')->nullable();
            $table->foreign('dcc_id')->references('dcc_id')->on('distributor_customer_class');

            $table->unsignedInteger('dcs_id')->nullable();
            $table->foreign('dcs_id')->references('dcs_id')->on('distributor_customer_segment');

            //
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
            $table->dropForeign(['dcc_id']);
            $table->dropColumn('dcc_id');

            $table->dropForeign(['dcs_id']);
            $table->dropColumn('dcs_id');

        });
    }
}

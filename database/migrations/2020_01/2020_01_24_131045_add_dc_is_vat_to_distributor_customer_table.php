<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDcIsVatToDistributorCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {
            $table->smallInteger('dc_is_vat')->nullable()->change();

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
            //
            $table->smallInteger('dc_is_vat')->nullable(false)->change();

        });
    }
}

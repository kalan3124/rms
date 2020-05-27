<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatPercentageToDistributorCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {
            //
            $table->decimal('dc_vat_percentage')->nullable();
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
            $table->dropColumn('dc_vat_percentage');
        });
    }
}

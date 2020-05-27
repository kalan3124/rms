<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDcVatPercentageToDistributorCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_customer', function (Blueprint $table) {
            $table->dropColumn('dc_vat_percentage');

            $table->tinyInteger('dc_is_vat')->default(0);
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
            $table->decimal('dc_vat_percentage')->nullable();

            $table->dropColumn('dc_is_vat');
        });
    }
}

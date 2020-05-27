<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDirectStatusToDistributorInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            $table->tinyInteger('di_is_direct')->default(0)->comment('0=No, 1= Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            $table->dropColumn('di_is_direct');
        });
    }
}

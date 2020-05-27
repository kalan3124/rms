<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTeamTableAddedColumnDistributorInvoiceLine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->decimal('unit_price_no_tax',10,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->dropcolumn('unit_price_no_tax');
        });
    }
}

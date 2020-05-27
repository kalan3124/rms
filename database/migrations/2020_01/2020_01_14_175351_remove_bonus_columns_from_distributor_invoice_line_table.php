<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveBonusColumnsFromDistributorInvoiceLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->dropColumn('dil_bns_qty');

            $table->dropForeign(['bns_id']);
            $table->dropColumn('bns_id');
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
            $table->integer('dil_bns_qty');

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');
        });
    }
}

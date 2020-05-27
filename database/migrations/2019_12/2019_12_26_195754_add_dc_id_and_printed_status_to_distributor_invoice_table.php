<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDcIdAndPrintedStatusToDistributorInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            
            $table->unsignedInteger('dc_id')->nullable();
            $table->foreign('dc_id')->references('dc_id')->on('distributor_customer');

            $table->timestamp('di_printed_at')->nullable();
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
            $table->dropForeign(['dc_id']);
            $table->dropColumn('dc_id');
            $table->dropColumn('di_printed_at');
        });
    }
}

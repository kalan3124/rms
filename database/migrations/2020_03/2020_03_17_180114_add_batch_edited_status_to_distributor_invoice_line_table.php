<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBatchEditedStatusToDistributorInvoiceLineTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice_line', function (Blueprint $table) {
            $table->tinyInteger('dil_batch_edited')->default(0)->comment('Edited = 1');
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
            $table->dropColumn('dil_batch_edited');
        });
    }
}

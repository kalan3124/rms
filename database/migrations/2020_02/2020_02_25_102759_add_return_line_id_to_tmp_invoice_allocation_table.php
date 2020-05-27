<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnLineIdToTmpInvoiceAllocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmp_invoice_allocation', function (Blueprint $table) {
            $table->unsignedInteger('return_line_id')->nullable();
            $table->foreign('return_line_id')->references('return_line_id')->on('return_lines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tmp_invoice_allocation', function (Blueprint $table) {
            $table->dropForeign(['return_line_id']);
            $table->dropColumn('return_line_id');
        });
    }
}

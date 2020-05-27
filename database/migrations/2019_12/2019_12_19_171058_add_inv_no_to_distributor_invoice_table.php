<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvNoToDistributorInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_invoice', function (Blueprint $table) {
            $table->string('di_number');
            $table->unique('di_number');
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
            $table->dropUnique(['di_number']);
            $table->dropColumn('di_number');
        });
    }
}

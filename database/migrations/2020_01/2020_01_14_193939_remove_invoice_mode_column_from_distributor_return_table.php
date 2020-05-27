<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveInvoiceModeColumnFromDistributorReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->dropColumn('invoice_mode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            $table->tinyInteger('invoice_mode')->nullable();
        });
    }
}

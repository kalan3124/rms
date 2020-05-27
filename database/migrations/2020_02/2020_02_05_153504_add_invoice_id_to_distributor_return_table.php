<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceIdToDistributorReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return', function (Blueprint $table) {
            
            $table->unsignedInteger('di_id')->nullable();
            $table->foreign('di_id')->references('di_id')->on('distributor_invoice');

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
            $table->dropForeign(['di_id']);
            $table->dropColumn('di_id');
        });
    }
}

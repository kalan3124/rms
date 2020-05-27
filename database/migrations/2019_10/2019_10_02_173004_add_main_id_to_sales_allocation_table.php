<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainIdToSalesAllocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_allocation', function (Blueprint $table) {
            $table->unsignedInteger('sam_id')->nullable();
            $table->foreign('sam_id')->references('sam_id')->on('sales_allocation_main');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_allocation', function (Blueprint $table) {
            $table->dropForeign(['sam_id']);
            $table->dropColumn('sam_id');
        });
    }
}

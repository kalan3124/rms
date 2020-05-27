<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteFlatColumnsFromBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus', function (Blueprint $table) {
            $table->dropColumn('bns_start_date');
            $table->dropColumn('bns_end_date');
            $table->dropColumn('bns_qty');
            $table->dropColumn('bns_free_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus', function (Blueprint $table) {
            $table->date('bns_start_date');
            $table->date('bns_end_date');
            $table->integer('bns_qty');
            $table->integer('bns_free_qty');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateColumnsToBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus', function (Blueprint $table) {
            $table->date('bns_start_date');
            $table->date('bns_end_date');
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
            $table->dropColumn('bns_start_date');
            $table->dropColumn('bns_end_date');
        });
    }
}

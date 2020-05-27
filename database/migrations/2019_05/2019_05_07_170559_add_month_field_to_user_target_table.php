<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMonthFieldToUserTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_target', function (Blueprint $table) {
            $table->integer('ut_year');
            $table->tinyInteger('ut_month');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_target', function (Blueprint $table) {
            $table->dropColumn('ut_year');
            $table->dropColumn('ut_month');
        });
    }
}

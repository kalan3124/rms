<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateColumnToMonthWiseAchievementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('month_wise_achievement', function (Blueprint $table) {
            $table->integer('mwa_day')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('month_wise_achievement', function (Blueprint $table) {
            $table->integer('mwa_day')->default(0);
        });
    }
}

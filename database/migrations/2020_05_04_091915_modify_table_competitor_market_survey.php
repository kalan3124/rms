<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTableCompetitorMarketSurvey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('competitor_market_survey', function (Blueprint $table) {
            $table->date("from")->nullable();
            $table->date("to")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('competitor_market_survey', function (Blueprint $table) {
            $table->dropColumn('from');
            $table->dropColumn('to');
        });
    }
}

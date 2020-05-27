<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionsAndSubTownsToUserAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_areas', function (Blueprint $table) {
            $table->unsignedInteger('rg_id')->nullable();
            $table->foreign('rg_id')->references('rg_id')->on('region');
            
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_areas', function (Blueprint $table) {
            $table->dropForeign(['rg_id']);
            $table->dropForeign(['sub_twn_id']);
            $table->dropColumn('rg_id');
            $table->dropColumn('sub_twn_id');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeHierarchyOfStandardItineraryDateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('standard_itinerary_date_area', function (Blueprint $table) {
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
            $table->unsignedInteger('rg_id')->nullable();
            $table->foreign('rg_id')->references('rg_id')->on('region');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('standard_itinerary_date_area', function (Blueprint $table) {
            $table->dropForeign(['sub_twn_id']);
            $table->dropForeign(['rg_id']);
            $table->dropColumn('sub_twn_id');
            $table->dropColumn('rg_id');
            
        });
    }
}

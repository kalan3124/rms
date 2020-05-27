<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangedIdToTmpUserItinerarySubTownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmp_user_itinerary_sub_town', function (Blueprint $table) {
            $table->unsignedInteger('idc_id')->nullable();
            $table->foreign('idc_id')->references('idc_id')->on('itinerary_date_changes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tmp_user_itinerary_sub_town', function (Blueprint $table) {
            $table->dropForeign(['idc_id']);
            $table->dropColumn('idc_id');
        });
    }
}

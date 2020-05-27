<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyItineraryDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary_day_type',function(Blueprint $table){
            $table->dropForeign(['i_id']);
            $table->renameColumn('i_id','id_id');
            $table->foreign('id_id')->references('id_id')->on('itinerary_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itinerary_day_type',function(Blueprint $table){
            $table->dropForeign(['id_id']);
            $table->renameColumn('id_id','i_id');
            $table->foreign('i_id')->references('i_id')->on('itinerary');
        });
    }
}

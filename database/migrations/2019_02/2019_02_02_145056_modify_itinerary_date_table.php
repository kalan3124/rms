<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary_date',function(Blueprint $table){
            $table->renameColumn('i_bata','id_bata');
            $table->renameColumn('i_mileage','id_mileage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itinerary_date',function(Blueprint $table){
            $table->renameColumn('id_bata','i_bata');
            $table->renameColumn('id_mileage','i_mileage');
        });
    }
}

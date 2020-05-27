<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ForeignBataTypeWithStandardItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('standard_itinerary_date',function(Blueprint $table){
            $table->dropColumn('sid_bata');
            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('standard_itinerary_date',function(Blueprint $table){
            $table->dropForeign(['bt_id']);
            $table->dropColumn('bt_id');
            $table->decimal('sid_bata',10,2)->default(0)->change();
        });
    }
}

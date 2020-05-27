<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ForeignItineraryDateWithStandardItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary_date', function (Blueprint $table) {
            $table->dropColumn('id_bata');
            $table->dropColumn('id_mileage');
            
            $table->unsignedInteger('sid_id')->nullable();
            $table->foreign('sid_id')->references('sid_id')->on('standard_itinerary_date');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itinerary_date',function($table){
            $table->decimal('id_bata',10,2)->default(0);
            $table->decimal('id_mileage',10,2)->default(0);
            $table->dropForeign(['sid_id']);
            $table->dropColumn('sid_id');
        });
    }
}

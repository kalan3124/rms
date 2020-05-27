<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJointFieldWorkerBataTypesAndMileageColumnsToItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary_date', function (Blueprint $table) {
            $table->decimal('id_mileage',10,2)->nullable()->comment("JFW");
            $table->unsignedInteger('bt_id')->nullable()->comment('JFW');
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
        Schema::table('itinerary_date', function (Blueprint $table) {
            $table->dropColumn('id_mileage');
            $table->dropForeign(['bt_id']);
            $table->dropColumn('bt_id');
        });
    }
}

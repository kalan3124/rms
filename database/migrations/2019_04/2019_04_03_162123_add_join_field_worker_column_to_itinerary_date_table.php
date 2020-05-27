<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJoinFieldWorkerColumnToItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary_date', function (Blueprint $table) {
            $table->unsignedInteger('u_id')->nullable()->comment("Joint Field Worker");
            $table->foreign('u_id')->references('id')->on('users');
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
            $table->dropForeign(['u_id']);
            $table->dropColumn('u_id');
        });
    }
}

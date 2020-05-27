<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovingColumnsToIndividualItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('itinerary', function (Blueprint $table) {
            $table->unsignedInteger('i_aprvd_u_id')->nullable();
            $table->foreign('i_aprvd_u_id')->references('id')->on('users');
            
            $table->timestamp('i_aprvd_at')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('itinerary', function (Blueprint $table) {
            $table->dropForeign(['i_aprvd_u_id']);
            $table->dropColumn('i_aprvd_u_id');
            $table->dropColumn('i_aprvd_at');
        });
    }
}

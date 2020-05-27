<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddItineraryTypeToSfaItineraryDateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_itinerary_date', function (Blueprint $table) {
            $table->tinyInteger('s_id_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sfa_itinerary_date', function (Blueprint $table) {
            $table->dropColumn('s_id_type');
        });
    }
}

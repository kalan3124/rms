<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJfwToTmpItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmp_user_itinerary_sub_town', function (Blueprint $table) {
            $table->unsignedInteger('uist_jfw_id')->nullable();
            $table->foreign('uist_jfw_id')->references('id')->on('users');
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
            $table->foreign(['uist_jfw_id']);
            $table->dropColumn('uist_jfw_id');
        });
    }
}

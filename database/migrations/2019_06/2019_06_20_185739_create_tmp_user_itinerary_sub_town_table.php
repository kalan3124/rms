<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmpUserItinerarySubTownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_user_itinerary_sub_town', function (Blueprint $table) {
            $table->increments('uist_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
            $table->unsignedInteger('arp_id')->nullable();
            $table->foreign('arp_id')->references('arp_id')->on('additional_route_plans');
            $table->unsignedInteger('sid_id')->nullable();
            $table->foreign('sid_id')->references('sid_id')->on('standard_itinerary_date');
            $table->unsignedInteger('i_id')->nullable();
            $table->foreign('i_id')->references('i_id')->on('itinerary');
            $table->unsignedInteger('id_id')->nullable();
            $table->foreign('id_id')->references('id_id')->on('itinerary_date');
            $table->integer('uist_year');
            $table->integer('uist_month');
            $table->integer('uist_date');
            $table->tinyInteger('uist_approved')->default(0)->comment('0=no 1=yes');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tmp_user_itinerary_sub_town');
    }
}

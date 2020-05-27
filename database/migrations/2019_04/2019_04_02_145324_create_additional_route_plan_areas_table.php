<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdditionalRoutePlanAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_route_plan_areas', function (Blueprint $table) {
            $table->increments('arpa_id');
            
            $table->unsignedInteger('arp_id')->nullable();
            $table->foreign('arp_id')->references('arp_id')->on('additional_route_plans');

            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
            
            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');

            $table->unsignedInteger('ar_id')->nullable();
            $table->foreign('ar_id')->references('ar_id')->on('area');

            $table->unsignedInteger('rg_id')->nullable();
            $table->foreign('rg_id')->references('rg_id')->on('region');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('dis_id')->on('district');

            $table->unsignedInteger('pv_id')->nullable();
            $table->foreign('pv_id')->references('pv_id')->on('province');

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
        Schema::dropIfExists('additional_route_plan_areas');
    }
}

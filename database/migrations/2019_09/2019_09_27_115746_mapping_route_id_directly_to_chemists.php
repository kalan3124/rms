<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MappingRouteIdDirectlyToChemists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->unsignedInteger('route_id')->nullable();
            $table->foreign('route_id')->references('route_id')->on('sfa_route');
        });

        Schema::dropIfExists('sfa_route_has_chemists');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('sfa_route_has_chemists', function (Blueprint $table) {
            $table->increments('sfa_routec_id');

            $table->unsignedInteger('route_id')->nullable();
            $table->foreign('route_id')->references('route_id')->on('sfa_route');
            
            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');

            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::table('chemist', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropColumn(['route_id']);
        });

    }
}

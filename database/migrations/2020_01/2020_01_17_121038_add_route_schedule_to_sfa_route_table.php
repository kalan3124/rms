<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRouteScheduleToSfaRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_route', function (Blueprint $table) {
            //
            $table->integer('route_schedule')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sfa_route', function (Blueprint $table) {
            //
            $table->dropColumn('route_schedule');
        });
    }
}

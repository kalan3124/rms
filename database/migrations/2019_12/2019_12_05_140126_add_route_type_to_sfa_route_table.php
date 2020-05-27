<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRouteTypeToSfaRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_route', function (Blueprint $table) {
            $table->tinyInteger('route_type')->default(0)->comment('0=Sales Rep,1=Distributor');
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
            $table->dropColumn('route_type');
        });
    }
}

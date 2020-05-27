<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUIdToSfaRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_route', function (Blueprint $table) {
            $table->unsignedInteger('u_id')->nullable()->after('route_name');
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
        Schema::table('sfa_route', function (Blueprint $table) {
            $table->dropForeign(['u_id']);
            $table->dropColumn('u_id');
        });
    }
}

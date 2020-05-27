<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddArIdToSfaRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_route', function (Blueprint $table) {
            $table->unsignedInteger('ar_id')->nullable()->after('route_name');
            $table->foreign('ar_id')->references('ar_id')->on('area');
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
            $table->dropForeign(['ar_id']);
            $table->dropColumn('ar_id');
        });
    }
}

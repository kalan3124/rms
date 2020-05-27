<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTeamUserColummnInMemberPercentageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_percentage', function (Blueprint $table) {
            $table->dropForeign(['tmu_id']);
            $table->renameColumn('tmu_id','u_id');
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
        Schema::table('member_percentage', function (Blueprint $table) {
            $table->dropForeign(['u_id']);
            $table->renameColumn('u_id','tmu_id');
            $table->foreign('tmu_id')->references('tmu_id')->on('team_users');
        });
    }
}

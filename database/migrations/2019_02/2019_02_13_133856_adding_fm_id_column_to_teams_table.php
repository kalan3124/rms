<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddingFmIdColumnToTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedInteger('fm_id')->nullable();
            $table->foreign('fm_id')->references('id')->on('users');

            $table->renameColumn('team_id','tm_id');
            $table->renameColumn('team_code','tm_code');
            $table->renameColumn('team_name','tm_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['fm_id']);
            $table->dropColumn('fm_id');

            $table->renameColumn('tm_id','team_id');
            $table->renameColumn('tm_code','team_code');
            $table->renameColumn('tm_name','team_name');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameColumnsInTeamUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_users', function (Blueprint $table) {
            $table->renameColumn('tmd_id','tmu_id');
            $table->renameColumn('team_id','tm_id');
            $table->dropColumn('user_posision');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_users', function (Blueprint $table) {
            $table->renameColumn('tmu_id','tmd_id');
            $table->renameColumn('tm_id','team_id');
            $table->tinyInteger('user_posision')->default('0')->comment('1-leader, 2-member');
        });
    }
}

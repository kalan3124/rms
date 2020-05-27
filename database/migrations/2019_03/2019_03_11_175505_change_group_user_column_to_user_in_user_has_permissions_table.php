<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeGroupUserColumnToUserInUserHasPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['pgu_id']);
            $table->dropColumn('pgu_id');
            $table->unsignedInteger('u_id')->nullable();
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
        Schema::table('user_has_permissions', function (Blueprint $table) {
            $table->dropForeign(['u_id']);
            $table->dropColumn('u_id');
            $table->unsignedInteger('pgu_id')->nullable();
            $table->foreign('pgu_id')->references('pgu_id')->on('permission_group_user');
        });
    }
}

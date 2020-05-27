<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserHasPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->increments('uperm_id');
            $table->unsignedInteger('perm_id')->nullable();
            $table->foreign('perm_id')->references('perm_id')->on('permissions');
            $table->unsignedInteger('pgu_id')->nullable();
            $table->foreign('pgu_id')->references('pgu_id')->on('permission_group_user');
            $table->unsignedInteger('pg_id')->nullable();
            $table->foreign('pg_id')->references('pg_id')->on('permission_group');
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
        Schema::dropIfExists('user_has_permissions');
    }
}

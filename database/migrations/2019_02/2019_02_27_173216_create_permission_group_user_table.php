<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_group_user', function (Blueprint $table) {
            $table->increments('pgu_id');
            $table->unsignedInteger('pg_id')->nullable();
            $table->foreign('pg_id')->references('pg_id')->on('permission_group');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->unsignedInteger('u_tp_id')->nullable();
            $table->foreign('u_tp_id')->references('u_tp_id')->on('user_types');
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
        Schema::dropIfExists('permission_group_user');
    }
}

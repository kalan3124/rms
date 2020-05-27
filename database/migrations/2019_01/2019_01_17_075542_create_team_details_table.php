<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_details', function (Blueprint $table) {
            $table->increments('tmd_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->foreign('team_id')->references('team_id')->on('teams');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->tinyInteger('user_posision')->default('0')->comment('1-leader, 2-member');
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
        Schema::dropIfExists('team_details');
    }
}

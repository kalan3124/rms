<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_areas', function (Blueprint $table) {
            $table->increments('ua_id');

            $table->unsignedInteger('u_id');
            $table->foreign('u_id')->references('id')->on('users');
            $table->unsignedInteger('ar_id');
            $table->foreign('ar_id')->references('ar_id')->on('area');
            $table->unsignedInteger('dis_id');
            $table->foreign('dis_id')->references('dis_id')->on('district');
            $table->unsignedInteger('pv_id');
            $table->foreign('pv_id')->references('pv_id')->on('province');
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
        Schema::dropIfExists('user_areas');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamUserProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_user_products', function (Blueprint $table) {
            $table->increments('tmup_id');

            $table->unsignedInteger('tmu_id')->nullable();
            $table->foreign('tmu_id')->references('tmu_id')->on('team_users');
            
            $table->unsignedInteger('tmp_id')->nullable();
            $table->foreign('tmp_id')->references('tmp_id')->on('team_products');

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
        Schema::dropIfExists('team_user_products');
    }
}

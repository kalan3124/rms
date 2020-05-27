<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusDistributorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_distributor', function (Blueprint $table) {
            $table->increments('bnsd_id');

            $table->unsignedInteger('dis_id')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->unsignedInteger('bns_id')->nullable();
            $table->foreign('bns_id')->references('bns_id')->on('bonus');

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
        Schema::dropIfExists('bonus_distributor');
    }
}

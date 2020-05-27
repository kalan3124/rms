<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('town', function (Blueprint $table) {
            $table->increments('twn_id');
            $table->string('twn_name');
            $table->string('twn_short_name');
            $table->string('twn_code');
            $table->unsignedInteger('ar_id')->nullable();
            $table->foreign('ar_id')->references('ar_id')->on('area');
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
        Schema::dropIfExists('town');
    }
}

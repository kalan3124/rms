<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubTownTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_town', function (Blueprint $table) {
            $table->increments('sub_twn_id');
            $table->string('sub_twn_code')->nullable();
            $table->string('sub_twn_name')->nullable();

            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');

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
        Schema::dropIfExists('sub_town');
    }
}

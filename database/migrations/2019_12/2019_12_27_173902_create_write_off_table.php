<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWriteOffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('write_off', function (Blueprint $table) {
            $table->increments('wo_id');

            $table->string('wo_no')->nullable();

            $table->unsignedInteger('dis_id')->comment('distributor')->nullable();
            $table->foreign('dis_id')->references('id')->on('users');

            $table->dateTime('wo_date');

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
        Schema::dropIfExists('write_off');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableAccSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acc_settings', function (Blueprint $table) {
            $table->increments('st_id');
            $table->string('st_type')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('duration_type')->nullable()->comment('1- Days, 2- time');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acc_settings');
    }
}

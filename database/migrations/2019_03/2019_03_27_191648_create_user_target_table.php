<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_target', function (Blueprint $table) {
            $table->increments('ut_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->decimal('ut_value',10,2);
            $table->integer('ut_qty');
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
        Schema::dropIfExists('user_target');
    }
}

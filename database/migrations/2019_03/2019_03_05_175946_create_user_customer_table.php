<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCustomerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_customer', function (Blueprint $table) {
            $table->increments('uc_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');
            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');
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
        Schema::dropIfExists('user_customer');
    }
}

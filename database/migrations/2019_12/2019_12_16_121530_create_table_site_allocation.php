<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSiteAllocation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_allocation', function (Blueprint $table) {
            $table->increments('site_allo_id');

            $table->unsignedInteger('site_id')->nullable();
            $table->foreign('site_id')->references('site_id')->on('site');

            $table->unsignedInteger('sr_id')->nullable();
            $table->foreign('sr_id')->references('id')->on('users');
            
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
        Schema::dropIfExists('site_allocation');
    }
}

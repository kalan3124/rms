<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaItineraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_itinerary', function (Blueprint $table) {
            $table->increments('s_i_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->integer('s_i_year');
            $table->tinyInteger('s_i_month');

            $table->timestamp('s_i_aprvd_at')->nullable();
            $table->unsignedInteger('s_aprvd_u_id')->nullable();
            $table->foreign('s_aprvd_u_id')->references('id')->on('users');          
            
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
        Schema::dropIfExists('sfa_itinerary');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSfaItineraryDateJfwTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sfa_itinerary_date_jfw', function (Blueprint $table) {
            $table->increments('s_idj_id');

            $table->unsignedInteger('s_id_id')->nullable();
            $table->foreign('s_id_id')->references('s_id_id')->on('sfa_itinerary_date');
            
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->time('s_idj_from');
            $table->time('s_idj_to');
            
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
        Schema::dropIfExists('sfa_itinerary_date_jfw');
    }
}

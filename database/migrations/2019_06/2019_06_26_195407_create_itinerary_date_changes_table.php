<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItineraryDateChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itinerary_date_changes', function (Blueprint $table) {
            $table->increments('idc_id');
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');
            $table->date('idc_date');
            $table->decimal('idc_mileage',10,2);
            $table->unsignedInteger('bt_id')->nullable();
            $table->foreign('bt_id')->references('bt_id')->on('bata_type');
            $table->unsignedInteger('idc_aprvd_u_id')->nullable();
            $table->foreign('idc_aprvd_u_id')->references('id')->on('users');
            $table->timestamp('idc_aprvd_at')->nullable();
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
        Schema::dropIfExists('itinerary_date_changes');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtherHospitalStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('other_hospital_staff', function (Blueprint $table) {
            $table->increments('hos_stf_id');
            $table->string('hos_stf_name');
            $table->tinyInteger('gender')->default('0')->comment('1-Male, 2-Female');
            $table->date('date_of_birth');
            $table->string('phone_no');
            $table->string('mobile_no');

            $table->unsignedInteger('hos_stf_cat_id')->nullable();
            $table->foreign('hos_stf_cat_id')->references('hos_stf_cat_id')->on('hospital_staff_category');

            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');

            $table->unsignedInteger('ins_id')->nullable();
            $table->foreign('ins_id')->references('ins_id')->on('institutions');

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
        Schema::dropIfExists('other_hospital_staff');
    }
}

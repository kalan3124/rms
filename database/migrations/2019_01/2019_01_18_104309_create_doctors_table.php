<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->increments('doc_id');
            $table->string('doc_name');

            $table->unsignedInteger('doc_spc_id');
            $table->foreign('doc_spc_id')->references('doc_spc_id')->on('doctor_speciality');

            $table->string('slmc_no');
            $table->tinyInteger('gender')->default('0')->comment('1-Male, 2-Female');
            $table->date('date_of_birth');
            $table->string('phone_no');
            $table->string('mobile_no');

            $table->unsignedInteger('doc_class_id');
            $table->foreign('doc_class_id')->references('doc_class_id')->on('doctor_classes');

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
        Schema::dropIfExists('doctors');
    }
}

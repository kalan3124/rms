<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMrDoctorCreationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mr_doctor_creation', function (Blueprint $table) {
            $table->increments('mr_doc_id');
            
            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id')->references('id')->on('users');

            $table->string('doc_code')->nullable();
            $table->string('doc_name')->nullable();
            $table->string('slmc_no')->nullable();
            $table->integer('phone_no')->nullable();
            $table->integer('mobile_no')->nullable();
            $table->tinyInteger('gender')->nullable()->comment('0-Male, 1-Female');
            $table->date('date_of_birth');

            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');

            $table->unsignedInteger('doc_class_id');
            $table->foreign('doc_class_id')->references('doc_class_id')->on('doctor_classes');

            $table->unsignedInteger('doc_spc_id');
            $table->foreign('doc_spc_id')->references('doc_spc_id')->on('doctor_speciality');

            $table->unsignedInteger('ins_id')->nullable();
            $table->foreign('ins_id')->references('ins_id')->on('institutions');

            $table->timestamp('added_date')->nullable();
            $table->string('app_version')->nullable();

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
        Schema::dropIfExists('mr_doctor_creation');
    }
}

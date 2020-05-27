<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstitutionAssignmentForDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('institution_assignment_for_doctors', function (Blueprint $table) {
            $table->increments('ins_ass_id');

            $table->unsignedInteger('doc_id');
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('ins_id');
            $table->foreign('ins_id')->references('ins_id')->on('institutions');

            $table->unsignedInteger('twn_id');
            $table->foreign('twn_id')->references('twn_id')->on('town');

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
        Schema::dropIfExists('institution_assignment_for_doctors');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChangesToMrDoctorCreationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mr_doctor_creation', function (Blueprint $table) {
            $table->unsignedInteger('date_of_birth')->nullable()->change();
            $table->unsignedInteger('doc_class_id')->nullable()->change();
            $table->unsignedInteger('doc_spc_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mr_doctor_creation', function (Blueprint $table) {
            $table->unsignedInteger('date_of_birth')->nullable(false)->change();
            $table->unsignedInteger('doc_class_id')->nullable(false)->change();
            $table->unsignedInteger('doc_spc_id')->nullable(false)->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeOfOtherHospitalCodeInOtherHospitalStaff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('other_hospital_staff', function (Blueprint $table) {
            $table->string('hos_stf_code')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('other_hospital_staff', function (Blueprint $table) {
            $table->softDeletes('hos_stf_code')->change();            
        });
    }
}

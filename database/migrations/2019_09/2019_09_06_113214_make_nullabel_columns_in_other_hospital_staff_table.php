<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeNullabelColumnsInOtherHospitalStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('other_hospital_staff', function (Blueprint $table) {
            $table->smallInteger('gender')->tinyInteger('gender')->default('0')->comment('1-Male, 2-Female')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->string('phone_no')->nullable()->change();
            $table->string('mobile_no')->nullable()->change();
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
            $table->smallInteger('gender')->tinyInteger('gender')->default('0')->comment('1-Male, 2-Female')->nullable(FALSE)->change();
            $table->date('date_of_birth')->nullable(FALSE)->change();
            $table->string('phone_no')->nullable(FALSE)->change();
            $table->string('mobile_no')->nullable(FALSE)->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppVersionColumnToMrDoctorTransportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mr_doctor_transport', function (Blueprint $table) {
            $table->string('app_version')->nullable()->after('end_time'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mr_doctor_transport', function (Blueprint $table) {
            $table->dropColumn(['app_version']);
        });
    }
}

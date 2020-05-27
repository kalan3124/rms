<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerifiedAtColumnToMrDoctorCreationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mr_doctor_creation', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('app_version');
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
            $table->dropColumn('verified_at');
        });
    }
}

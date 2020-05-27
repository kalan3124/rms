<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUniqueInUserAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_attendance', function (Blueprint $table) {
            $table->unique(['check_in_time','u_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_attendance', function (Blueprint $table) {
            $table->dropUnique(['check_in_time','u_id']);
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUserAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_attendance', function (Blueprint $table) {
            $table->decimal('check_in_lat', 20,15)->nullable()->change();
            $table->decimal('check_in_lon', 20,15)->nullable()->change();
            $table->integer('check_in_mileage')->nullable()->change();
            $table->float('check_in_battery', 8, 2)->nullable()->change();

            $table->decimal('check_out_lat', 20,15)->nullable()->change();
            $table->decimal('check_out_lon', 20,15)->nullable()->change();
            $table->integer('check_out_mileage')->nullable()->change();
            $table->float('check_out_battery', 8, 2)->nullable()->change();
            $table->integer('checkout_status')->nullable()->comments('1 - Checkout')->after('check_out_loc_type');
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
            $table->decimal('check_in_lat', 20,15)->nullable(false)->change();
            $table->decimal('check_in_lon', 20,15)->nullable(false)->change();
            $table->integer('check_in_mileage')->nullable(false)->change();
            $table->float('check_in_battery', 8, 2)->nullable(false)->change();

            $table->decimal('check_out_lat', 20,15)->nullable(false)->change();
            $table->decimal('check_out_lon', 20,15)->nullable(false)->change();
            $table->integer('check_out_mileage')->nullable(false)->change();
            $table->float('check_out_battery', 8, 2)->nullable(false)->change();
            $table->dropColumn('checkout_status');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDoctorPromotionByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->unsignedInteger('u_id')->nullable()->after('dpbmr_id');
            $table->foreign('u_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->dropForeign(['u_id']);
            $table->dropColumn(['u_id']);
        });
    }
}

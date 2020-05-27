<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImgUrlDoctorPromotionByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('bat_lvl');
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
            $table->dropColumn(['image_url']);
        });
    }
}

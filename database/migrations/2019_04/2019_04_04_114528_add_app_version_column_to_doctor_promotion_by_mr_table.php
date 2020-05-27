<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppVersionColumnToDoctorPromotionByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->string('app_version')->nullable()->after('image_url');
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
            $table->dropColumn(['app_version']);
        });
    }
}

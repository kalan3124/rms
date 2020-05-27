<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalValueToDoctorPromotionByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->decimal('promo_value',10,2)->nullable()->after('head_count');
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
            $table->dropColumn(['promo_value']);
        });
    }
}

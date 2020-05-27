<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorPromotionByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_promotion_by_mr', function (Blueprint $table) {
            $table->increments('dpbmr_id');

            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');

            $table->unsignedInteger('promo_id')->nullable();
            $table->foreign('promo_id')->references('promo_id')->on('promotion');

            $table->unsignedInteger('vt_id')->nullable()->comment('visited place');
            $table->foreign('vt_id')->references('vt_id')->on('visit_type');

            $table->integer('head_count')->nullable();
            $table->timestamp('promo_date')->nullable();
            $table->decimal('promo_lon', 10, 7)->nullable();
            $table->decimal('promo_lat', 10, 7)->nullable();
            $table->integer('bat_lvl')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctor_promotion_by_mr');
    }
}

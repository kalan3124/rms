<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDoctorPromotionDetailsByMrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctor_promotion_details_by_mr', function (Blueprint $table) {
            $table->increments('dpdbmr_id');

            $table->unsignedInteger('dpbmr_id')->nullable();
            $table->foreign('dpbmr_id')->references('dpbmr_id')->on('doctor_promotion_by_mr');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

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
        Schema::dropIfExists('doctor_promotion_details_by_mr');
    }
}

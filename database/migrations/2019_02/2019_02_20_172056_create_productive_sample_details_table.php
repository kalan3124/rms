<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductiveSampleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productive_sample_details', function (Blueprint $table) {
            $table->increments('pro_smpd_id');

            $table->unsignedInteger('pro_visit_id')->nullable();
            $table->foreign('pro_visit_id')->references('pro_visit_id')->on('productive_visit');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('sampling_reason_id')->nullable();
            $table->foreign('sampling_reason_id')->references('rsn_id')->on('reason');

            $table->unsignedInteger('detailing_reason_id')->nullable();
            $table->foreign('detailing_reason_id')->references('rsn_id')->on('reason');

            $table->unsignedInteger('promotion_reason_id')->nullable();
            $table->foreign('promotion_reason_id')->references('rsn_id')->on('reason');

            $table->integer('qty');
            $table->longText('remark')->nullable();

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
        Schema::dropIfExists('productive_sample_details');
    }
}

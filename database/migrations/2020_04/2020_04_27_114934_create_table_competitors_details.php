<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompetitorsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitors_details', function (Blueprint $table) {
            $table->increments('com_details_id');
            $table->unsignedInteger('com_survey_id')->nullable();
            $table->foreign('com_survey_id')->references('com_survey_id')->on('competitor_market_survey');

            $table->unsignedInteger('cmp_id')->nullable();
            $table->foreign('cmp_id')->references('cmp_id')->on('competitors');

            $table->decimal('total_purchase_value',10,2)->comment('Total Purchase Value')->nullable();
            $table->integer('visit_frequency')->nullable();
            $table->integer('visit_day_Of_week')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('competitors_details');
    }
}

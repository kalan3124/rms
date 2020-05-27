<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableCompetitorMarketSurvey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('competitor_market_survey', function (Blueprint $table) {
            $table->increments('com_survey_id');
            $table->unsignedInteger('chemist_id')->nullable();
            $table->foreign('chemist_id')->references('chemist_id')->on('chemist');
            $table->dateTime('survey_time')->nullable();
            $table->decimal('lat', 20,15)->nullable();
            $table->decimal('lon', 20,15)->nullable();
            $table->float('battery', 8, 2)->nullable();
            $table->string('owner_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->integer('contact_1')->nullable();
            $table->integer('contact_2')->nullable();
            $table->string('email')->nullable();
            $table->integer('no_of_staff')->nullable();
            $table->decimal('tot_pur_month',10,2)->comment('Total Purchases of the Pharmacy Per Month')->nullable();
            $table->decimal('pharmacy_pur_month',10,2)->comment('Purchases of Pharmaceuitical Product of the Pharmacy Per Month')->nullable();
            $table->decimal('val_shl_pro_thirdPartyDis',10,2)->comment('value of SHL Product Brought (Purchased) from 3rd party Distributors')->nullable();
            $table->decimal('val_tot_pro_Redistributed',10,2)->comment('value of Total Pharmaceuitical Products Redistributed')->nullable();
            $table->decimal('val_shl_pro_Redistributed',10,2)->comment('Value of SHL Products Redistributed by the Pharmacy')->nullable();

            $table->decimal('pharmacy_sales_day',10,2)->nullable();
            $table->decimal('pharmacy_sales_month',10,2)->nullable();

            $table->string('remark')->nullable();
            $table->tinyInteger('activeStatus')->nullable();

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
        Schema::dropIfExists('competitor_market_survey');
    }
}

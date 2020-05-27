<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixedDefaultValueIssueInDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('day_type', function (Blueprint $table) {
            $table->smallInteger('dt_is_working')->tinyInteger('dt_is_working')->default('0')->comment("Has routes or not,1=Yes")->change();
            $table->smallInteger('dt_bata_enabled')->tinyInteger("dt_bata_enabled")->default('0')->comment("1=Enabled")->change();
            $table->smallInteger('dt_mileage_enabled')->tinyInteger("dt_mileage_enabled")->default('0')->comment("1=Enabled")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('day_type', function (Blueprint $table) {
            $table->smallInteger('dt_is_working')->tinyInteger('dt_is_working')->default(0)->comment("Has routes or not,1=Yes")->change();
            $table->smallInteger('dt_bata_enabled')->tinyInteger("dt_bata_enabled")->default(0)->comment("1=Enabled")->change();
            $table->smallInteger('dt_mileage_enabled')->tinyInteger("dt_mileage_enabled")->default(0)->comment("1=Enabled")->change();
        });
    }
}

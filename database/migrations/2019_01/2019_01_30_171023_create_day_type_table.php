<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDayTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('day_type', function (Blueprint $table) {
            $table->increments('dt_id');
            $table->string('dt_name');
            $table->string('dt_code');
            $table->integer('dt_color')->comment("R=1,G=2,B=3,BLCK=4,Y=5,P=6,Brwn=7,O=8");
            $table->tinyInteger('dt_is_working')->default('0')->comment("Has routes or not,1=Yes");
            $table->tinyInteger("dt_bata_enabled")->default('0')->comment("1=Enabled");
            $table->tinyInteger("dt_mileage_enabled")->default('0')->comment("1=Enabled");
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
        Schema::dropIfExists('day_type');
    }
}

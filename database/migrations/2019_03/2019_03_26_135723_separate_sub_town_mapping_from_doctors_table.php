<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SeparateSubTownMappingFromDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['sub_twn_id']);
            $table->dropColumn('sub_twn_id');
        });

        Schema::create('doctor_sub_towns', function (Blueprint $table){
            $table->increments('dst_id');

            $table->unsignedInteger('doc_id')->nullable();
            $table->foreign('doc_id')->references('doc_id')->on('doctors');
            
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
            
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
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
        });

        Schema::dropIfExists('doctor_sub_towns');
    }
}

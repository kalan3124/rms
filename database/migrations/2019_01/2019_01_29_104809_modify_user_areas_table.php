<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUserAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_areas',function(Blueprint $table){
            $table->unsignedInteger('ar_id')->nullable()->change();
            $table->unsignedInteger('dis_id')->nullable()->change();
            $table->unsignedInteger('pv_id')->nullable()->change();
            $table->unsignedInteger('twn_id')->nullable();
            $table->foreign('twn_id')->references('twn_id')->on('town');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_areas',function(Blueprint $table){
            $table->unsignedInteger('ar_id')->nullable(false)->change();
            $table->unsignedInteger('dis_id')->nullable(false)->change();
            $table->unsignedInteger('pv_id')->nullable(false)->change();
            $table->dropForeign(['twn_id']);
            $table->dropColumn('twn_id');
        });
    }
}

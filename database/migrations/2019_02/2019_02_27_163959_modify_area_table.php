<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('area', function (Blueprint $table) {
            $table->dropForeign(['dis_id']);
            $table->dropColumn('dis_id');

            $table->unsignedInteger('rg_id')->nullable()->after('ar_code');
            $table->foreign('rg_id')->references('rg_id')->on('region');
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('area', function (Blueprint $table) {
            $table->dropForeign(['rg_id']);
            $table->dropColumn('rg_id');

            $table->unsignedInteger('dis_id')->nullable()->after('ar_code');
            $table->foreign('dis_id')->references('dis_id')->on('district');
        });
    }
}

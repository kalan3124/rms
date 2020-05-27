<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bata_type', function (Blueprint $table) {
            $table->unsignedInteger('btc_id')->nullable();
            $table->foreign('btc_id')->references('btc_id')->on('bata_category');
            $table->unsignedInteger('divi_id')->nullable();
            $table->foreign('divi_id')->references('divi_id')->on('division');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bata_type', function (Blueprint $table) {
            $table->dropForeign(['btc_id']);
            $table->dropColumn('btc_id');
            $table->dropForeign(['divi_id']);
            $table->dropColumn('divi_id');
        });
    }
}

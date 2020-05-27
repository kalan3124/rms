<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnToUnproductiveVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unproductive_visit', function (Blueprint $table) {
            $table->unsignedInteger('hos_stf_id')->nullable()->after('app_version');
            $table->foreign('hos_stf_id')->references('hos_stf_id')->on('other_hospital_staff');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unproductive_visit', function (Blueprint $table) {
            $table->dropForeign(['hos_stf_id']);
            $table->dropColumn('hos_stf_id');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppVersionColumnToUnproductiveVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unproductive_visit', function (Blueprint $table) {
            $table->string('app_version')->nullable()->after('visited_place');
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
            $table->dropColumn(['app_version']);
        });
    }
}

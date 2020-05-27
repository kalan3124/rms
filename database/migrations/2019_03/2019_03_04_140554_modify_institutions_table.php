<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyInstitutionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropForeign(['twn_id']);
            $table->dropColumn('twn_id');

            $table->unsignedInteger('sub_twn_id')->nullable()->after('ins_cat_id');
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropForeign(['sub_twn_id']);
            $table->dropColumn('sub_twn_id');

            $table->unsignedInteger('twn_id')->nullable()->after('ins_cat_id');
            $table->foreign('twn_id')->references('twn_id')->on('town');
        });
    }
}

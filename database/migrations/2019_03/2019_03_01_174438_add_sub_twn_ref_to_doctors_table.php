<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubTwnRefToDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->unsignedInteger('sub_twn_id')->nullable();
            $table->foreign('sub_twn_id')->references('sub_twn_id')->on('sub_town');

            $table->string('doc_code');

            $table->unsignedInteger('doc_spc_id')->nullable()->change();
            $table->unsignedInteger('doc_class_id')->nullable()->change();

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
            $table->dropForeign(['sub_twn_id']);
            $table->dropColumn('sub_twn_id');

            $table->dropColumn('doc_code');

            $table->unsignedInteger('doc_spc_id')->nullable(false)->change();
            $table->unsignedInteger('doc_class_id')->nullable(false)->change();
        });
    }
}

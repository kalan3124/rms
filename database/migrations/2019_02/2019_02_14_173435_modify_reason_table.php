<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyReasonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reason', function (Blueprint $table) {
            $table->unsignedInteger('rsn_type')->comment('')->change();
            $table->foreign('rsn_type')->references('rsn_tp_id')->on('reason_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reason', function (Blueprint $table) {
            $table->dropForeign(['rsn_type']);
        });
    }
}

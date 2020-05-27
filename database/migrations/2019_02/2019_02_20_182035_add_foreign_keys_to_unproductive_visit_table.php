<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToUnproductiveVisitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unproductive_visit', function (Blueprint $table) {
            $table->unsignedInteger('reason_id')->nullable()->comment('0-Doctor, 1-Chemist')->change();
            $table->foreign('reason_id')->references('rsn_id')->on('reason');
            
            $table->unsignedInteger('visited_place')->nullable()->comment('0-Shedule, 1-UnShedule')->change();
            $table->foreign('visited_place')->references('vt_id')->on('visit_type');
            
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
            $table->dropForeign(['reason_id']);
            $table->dropForeign(['visited_place']);

            $table->integer('reason_id')->nullable()->comment('0-Doctor, 1-Chemist')->change();
            $table->integer('visited_place')->nullable()->comment('0-Shedule, 1-UnShedule')->change();

        });
    }
}

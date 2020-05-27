<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifiyTableSrExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_expenses', function (Blueprint $table) {
            $table->decimal('mileage',10,2)->nullable();
            $table->dateTime('aprroved')->nullable();
            $table->unsignedInteger('approved_u_id')->nullable();
            $table->foreign('approved_u_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sfa_expenses', function (Blueprint $table) {
            $table->dropForeign(['approved_u_id']);
            $table->dropColumn('approved_u_id');
        });
    }
}

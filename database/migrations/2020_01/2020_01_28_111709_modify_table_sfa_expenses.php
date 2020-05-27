<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTableSfaExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_expenses', function (Blueprint $table) {
            $table->decimal('def_actual_mileage',15,2)->nullable();
            $table->decimal('actual_mileage',15,2)->nullable();
            $table->decimal('mileage_amount',15,2)->nullable();
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
            $table->dropColumn('def_actual_mileage');
            $table->dropColumn('actual_mileage');
            $table->dropColumn('mileage_amount');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToDistributorPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_payments', function (Blueprint $table) {
            $table->string('c_no')->nullable();
            $table->string('c_bank')->nullable();
            $table->string('c_branch')->nullable();
            $table->date('c_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_payments', function (Blueprint $table) {
            $table->dropColumn('c_no');
            $table->dropColumn('c_bank');
            $table->dropColumn('c_branch');
            $table->dropColumn('c_date');
        });
    }
}

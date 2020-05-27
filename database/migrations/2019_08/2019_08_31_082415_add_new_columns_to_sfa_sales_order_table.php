<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToSfaSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sfa_sales_order', function (Blueprint $table) {
            $table->string('contract')->nullable()->after('app_version');
            $table->timestamp('integrated_at')->nullable()->after('contract');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sfa_sales_order', function (Blueprint $table) {
            $table->dropColumn('contract');
            $table->dropColumn('integrated_at');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDateFormatToSalesmanValidPartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salesman_valid_parts', function (Blueprint $table) {
            $table->dropColumn('from_date');
            $table->dropColumn('to_date');
        });

        Schema::table('salesman_valid_parts', function (Blueprint $table) {
            $table->dateTime('from_date')->nullable()->after('catalog_no');
            $table->dateTime('to_date')->nullable()->after('from_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salesman_valid_parts', function (Blueprint $table) {
            $table->dropColumn('from_date');
            $table->dropColumn('to_date');
        });

        Schema::table('salesman_valid_parts', function (Blueprint $table) {
            $table->timestamp('from_date')->nullable()->after('catalog_no');
            $table->timestamp('to_date')->nullable()->after('from_date');
        });
    }
}

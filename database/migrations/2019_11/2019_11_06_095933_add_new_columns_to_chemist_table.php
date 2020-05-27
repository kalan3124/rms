<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToChemistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->string('phone_no')->nullable()->after('mobile_number');
            $table->string('chemist_owner')->nullable()->after('phone_no');
            $table->decimal('credit_limit',15,2)->nullable()->after('chemist_owner');

            $table->string('mobile_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->dropColumn('phone_no');
            $table->dropColumn('chemist_owner');
            $table->dropColumn('credit_limit');

            $table->integer('mobile_number')->nullable()->change();
        });
    }
}

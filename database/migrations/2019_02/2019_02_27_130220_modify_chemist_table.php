<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyChemistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chemist', function (Blueprint $table) {
            $table->string('chemist_name')->nullable()->change();
            $table->string('chemist_code')->nullable()->change();
            $table->string('chemist_address')->nullable()->change();
            $table->string('telephone')->nullable()->change();
            $table->decimal('credit_amount', 8, 2)->nullable()->change();
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
            $table->string('chemist_name')->nullable(false)->change();
            $table->string('chemist_code')->nullable(false)->change();
            $table->string('chemist_address')->nullable(false)->change();
            $table->string('telephone')->nullable(false)->change();
            $table->decimal('credit_amount', 8, 2)->nullable(false)->change();
        });
    }
}

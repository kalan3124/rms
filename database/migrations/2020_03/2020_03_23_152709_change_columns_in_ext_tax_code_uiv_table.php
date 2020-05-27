<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsInExtTaxCodeUivTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ext_tax_code_uiv', function (Blueprint $table) {
            $table->dropColumn(['valid_from', 'valid_until']);
        });
        Schema::table('ext_tax_code_uiv', function (Blueprint $table) {
            $table->dateTime('valid_from')->after('fee_rate')->nullable();
            $table->dateTime('valid_until')->after('valid_from')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ext_tax_code_uiv', function (Blueprint $table) {
            $table->dropColumn(['valid_from', 'valid_until']);
        });
        Schema::table('ext_tax_code_uiv', function (Blueprint $table) {
            $table->timestamp('valid_from')->after('fee_rate')->nullable();
            $table->timestamp('valid_until')->after('valid_from')->nullable();
        });
    }
}

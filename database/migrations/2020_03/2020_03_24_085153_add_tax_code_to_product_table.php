<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxCodeToProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->unsignedInteger('tax_code_id')->nullable();
            $table->foreign('tax_code_id')->references('tax_code_id')->on('tax_codes');

            $table->string('tax_code')->nullable();
            $table->string('tax_code_desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->dropForeign(['tax_code_id']);
            $table->dropColumn(['tax_code_id','tax_code','tax_code_desc']);
        });
    }
}

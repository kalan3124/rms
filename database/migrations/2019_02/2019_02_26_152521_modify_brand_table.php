<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyBrandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brand', function (Blueprint $table) {
            $table->dropForeign(['product_family_id']);
            $table->dropColumn('product_family_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brand', function (Blueprint $table) {
            $table->unsignedInteger('product_family_id')->nullable()->after('brand_name');
            $table->foreign('product_family_id')->references('product_family_id')->on('product_family');
        });
    }
}

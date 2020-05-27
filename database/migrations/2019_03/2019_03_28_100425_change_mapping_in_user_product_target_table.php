<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeMappingInUserProductTargetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_product_target', function (Blueprint $table) {
            $table->dropForeign(['tmup_id']);
            $table->dropColumn('tmup_id');

            $table->unsignedInteger('product_id')->nullable();
            $table->foreign('product_id')->references('product_id')->on('product');

            $table->unsignedInteger('brand_id')->nullable();
            $table->foreign('brand_id')->references('brand_id')->on('brand');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_product_target', function (Blueprint $table) {
            $table->unsignedInteger('tmup_id')->nullable();
            $table->foreign('tmup_id')->references('tmup_id')->on('team_user_products');

            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');

            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
}

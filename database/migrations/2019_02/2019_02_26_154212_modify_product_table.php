<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->string('product_code')->nullable()->change();
            $table->string('product_short_name')->nullable()->change();
            $table->string('product_name')->nullable()->change();
            $table->unsignedInteger('principal_id')->nullable()->after('brand_id');
            $table->foreign('principal_id')->references('principal_id')->on('principal');

            $table->unsignedInteger('product_family_id')->nullable()->after('principal_id');
            $table->foreign('product_family_id')->references('product_family_id')->on('product_family');

            $table->unsignedInteger('divi_id')->nullable()->after('product_family_id');
            $table->foreign('divi_id')->references('divi_id')->on('division');
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
            $table->string('product_code')->nullable(false)->change();
            $table->string('product_short_name')->nullable(false)->change();
            $table->string('product_name')->nullable(false)->change();
            $table->dropForeign(['principal_id']);
            $table->dropColumn('principal_id');

            $table->dropForeign(['product_family_id']);
            $table->dropColumn('product_family_id');

            $table->dropForeign(['divi_id']);
            $table->dropColumn('divi_id');
        });
    }
}

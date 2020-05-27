<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProductFamilyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_family', function (Blueprint $table) {
            $table->string('product_family_code')->nullable()->after('product_family_id');
            $table->dropForeign(['principal_id']);
            $table->dropColumn('principal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_family', function (Blueprint $table) {
            $table->dropColumn('product_family_code');

            $table->unsignedInteger('principal_id')->nullable()->after('product_family_name');
            $table->foreign('principal_id')->references('principal_id')->on('principal');
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrincipalCodesToProductTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_product_target', function (Blueprint $table) {
            $table->unsignedInteger('principal_id')->nullable();
            $table->foreign('principal_id')->references('principal_id')->on('principal');
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
            $table->dropForeign(['principal_id']);
            $table->dropColumn('principal_id');
        });
    }
}

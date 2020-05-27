<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSalableColumnToDistributorReturnItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return_item', function (Blueprint $table) {
            $table->tinyInteger('dri_is_salable')->default(1)->comment('1=Yes,0=No');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_return_item', function (Blueprint $table) {
            $table->dropColumn('dri_is_salable');
        });
    }
}

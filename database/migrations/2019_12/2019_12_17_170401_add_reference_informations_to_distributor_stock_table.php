<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceInformationsToDistributorStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_stock', function (Blueprint $table) {
            $table->unsignedInteger('ds_ref_id');
            $table->tinyInteger('ds_ref_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('distributor_stock', function (Blueprint $table) {
            $table->dropColumn('ds_ref_id');
            $table->dropColumn('ds_ref_type');
        });
    }
}

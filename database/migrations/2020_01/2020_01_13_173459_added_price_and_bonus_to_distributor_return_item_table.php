<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedPriceAndBonusToDistributorReturnItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('distributor_return_item', function (Blueprint $table) {
            $table->decimal('dri_price',12,2);
            $table->integer('dri_bns_qty');
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
            $table->dropColumn('dri_price');
            $table->dropColumn('dri_bns_qty');
        });
    }
}
